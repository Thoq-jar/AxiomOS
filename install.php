#!/usr/bin/env php

<?php
function showAsciiArt(): void {
    echo "\033[38;5;208m";
    echo "
                   @@@@@@@@@
              @@@@@@@@@@@@@@@@@
           @@@@@@@@@@   @@@@@@@@@@
       @@@@@@@@@@@         @@@@@@@@@@@
     @@@@@@@@@      @@@@@      @@@@@@@@@
     @@@@@       @@@@@@@@@@@       @@@@@
     @@@@     @@@@@@@   @@@@@@@     @@@@
     @@@@     @@@          @@@@     @@@@
     @@@@     @@@           @@@     @@@@
     @@@@     @@@           @@@     @@@@
     @@@@     @@@@@       @@@@@     @@@@
     @@@@     @@@@@@@@@@@@@@@@@     @@@@
     @@@@@@@     @@@@@@@@@@@     @@@@@@@
     @@@@@@@@@@               @@@@@@@@@
        @@@@@@@@@@@       @@@@@@@@@@
            @@@@@@@@@@@@@@@@@@@@@
                @@@@@@@@@@@@@
                    @@@@@
    \033[0m\n";
}

function executeCommand($command): bool {
    exec($command . ' 2>/dev/null', $output, $returnCode);
    return $returnCode === 0;
}

function checkDependencies(): array {
    $dependencies = [
        'git' => ['name' => 'Git', 'required' => true]
    ];

    $results = [];
    foreach($dependencies as $cmd => $info) {
        $available = executeCommand("which $cmd");
        $results[$cmd] = [
            'name' => $info['name'],
            'available' => $available,
            'required' => $info['required']
        ];
    }

    return $results;
}

function showSpinner($message, $callback = null): void {
    $spinnerChars = ['⠋', '⠙', '⠹', '⠸', '⠼', '⠴', '⠦', '⠧', '⠇', '⠏'];
    $i = 0;
    $running = true;

    if($callback === null) {
        $startTime = microtime(true);
        while ((microtime(true) - $startTime) < 3) {
            $spinner = $spinnerChars[$i % count($spinnerChars)];
            echo "\r\033[38;5;208m$spinner $message\033[0m";
            flush();
            usleep(100000);
            $i++;
        }
    } else {
        while($running) {
            $spinner = $spinnerChars[$i % count($spinnerChars)];
            echo "\r\033[38;5;208m$spinner $message\033[0m";
            flush();

            $result = $callback();
            if($result !== null) {
                $running = false;
                if($result === true) {
                    echo "\r\033[38;5;214m✓ $message\033[0m\n";
                } else {
                    echo "\r\033[38;5;196m✗ $message failed\033[0m\n";
                }
                return;
            }

            usleep(100000);
            $i++;
        }
    }

    echo "\r\033[38;5;214m✓ $message\033[0m\n";
}

function showMenu($title, $options, $selected = [], $currentIndex = 0): void {
    system('clear');
    showAsciiArt();

    echo "\033[38;5;214m$title\033[0m\n\n";

    for($i = 0; $i < count($options); $i++) {
        $isSelected = in_array($i, $selected);
        $isCurrentIndex = ($i === $currentIndex);

        if($isSelected) {
            $circle = "\033[38;5;208m●\033[0m";
            $color = $isCurrentIndex ? "\033[48;5;52;38;5;214m" : "\033[38;5;214m";
        } else {
            $circle = "\033[37m○\033[0m";
            $color = $isCurrentIndex ? "\033[48;5;236;38;5;255m" : "\033[37m";
        }

        $cursor = $isCurrentIndex ? "\033[38;5;208m► \033[0m" : "  ";

        echo "$cursor$circle $color$options[$i]\033[0m\n";
    }

    echo "\n\033[38;5;208m";
    echo "Use SPACE to select/deselect, ENTER to confirm\n";
    echo "Arrow keys to navigate, Q to quit\033[0m\n";
}

function getUserSelection($title, $options, $multiSelect = true) {
    $selected = [];
    $currentIndex = 0;

    while(true) {
        if($multiSelect) {
            showMenu($title, $options, $selected, $currentIndex);
        } else {
            showMenu($title, $options, [$currentIndex], $currentIndex);
        }

        echo "\n\033[38;5;208m> \033[0m";

        $key = readKey();

        switch($key) {
            case "\033[A":
                $currentIndex = max(0, $currentIndex - 1);
                break;
            case "\033[B":
                $currentIndex = min(count($options) - 1, $currentIndex + 1);
                break;
            case " ":
                if($multiSelect) {
                    $key = array_search($currentIndex, $selected);
                    if($key !== false) {
                        unset($selected[$key]);
                        $selected = array_values($selected);
                    } else {
                        $selected[] = $currentIndex;
                    }
                }
                break;
            case "\n":
            case "\r":
                return $multiSelect ? $selected : [$currentIndex];
            case "q":
            case "Q":
                echo "\033[38;5;208mInstallation cancelled.\033[0m\n";
                exit(0);
        }
    }
}

function readKey(): false|string {
    system('stty cbreak -echo');
    $key = fgetc(STDIN);

    if($key === "\033") {
        $key .= fgetc(STDIN);
        $key .= fgetc(STDIN);
    }

    system('stty -cbreak echo');
    return $key;
}

function showProgress($message, $current, $total): void {
    $barLength = 50;
    $percentage = ($current / $total) * 100;
    $filled = intval($percentage * $barLength / 100);
    $empty = $barLength - $filled;

    $bar = str_repeat("█", $filled) . str_repeat("░", $empty);
    $percentageText = number_format($percentage, 1);

    echo "\r\033[2K\033[38;5;208m$message\033[0m [\033[38;5;214m$bar\033[0m] $percentageText%";
    flush();
}

function cloneRepository($repoUrl, $targetPath): bool {
    if(!is_dir(dirname($targetPath))) {
        mkdir(dirname($targetPath), 0755, true);
    }

    if(is_dir($targetPath)) {
        exec("rm -rf $targetPath");
    }

    $command = "git clone $repoUrl $targetPath 2>&1";
    $output = [];
    $returnCode = 0;

    exec($command, $output, $returnCode);

    return $returnCode === 0;
}

function buildProject($projectPath): bool {
    $originalDir = getcwd();
    chdir($projectPath);

    $buildCommands = [
        'composer install --no-interaction --prefer-dist --optimize-autoloader',
        'composer require doctrine/dbal --no-interaction',
        'cp .env.example .env',
        'php artisan key:generate --force',
        'touch database/database.sqlite',
        'php artisan migrate:fresh --force',
        'php artisan session:table',
        'php artisan migrate --force'
    ];

    foreach($buildCommands as $command) {
        exec($command . ' 2>&1', $output, $returnCode);
        if($returnCode !== 0) {
            if(!str_contains($command, 'session:table') && !str_contains($command, 'doctrine/dbal')) {
                chdir($originalDir);
                echo "\n\033[38;5;196mBuild command failed: $command\033[0m\n";
                echo "\033[38;5;196mOutput: " . implode("\n", $output) . "\033[0m\n";
                return false;
            }
        }

        $output = [];
    }

    chdir($originalDir);
    return true;
}

function createLaunchScript($projectPath): bool {
    $scriptContent = "#!/bin/bash
cd $projectPath
php axiom \$1
";

    $scriptPath = "/usr/local/bin/axiom";

    if(file_put_contents($scriptPath, $scriptContent) === false) {
        return false;
    }

    chmod($scriptPath, 0755);

    return true;
}

function createStartupScript($projectPath, $serviceName): bool {
    $serviceContent = "[Unit]
Description=Axiom OS Service
After=network.target

[Service]
Type=simple
User=root
WorkingDirectory=$projectPath
ExecStart=/usr/local/bin/axiom-os
Restart=always
RestartSec=10

[Install]
WantedBy=multi-user.target
";

    $servicePath = "/etc/systemd/system/$serviceName.service";

    if(file_put_contents($servicePath, $serviceContent) === false) {
        return false;
    }

    exec('systemctl daemon-reload', $output, $returnCode);
    if($returnCode !== 0) return false;

    exec("systemctl enable $serviceName", $output, $returnCode);

    return $returnCode === 0;
}

function installDependencies(): bool {
    $packages = ['git'];

    if(executeCommand('which apt-get')) {
        $installCmd = 'apt-get update && apt-get install -y ' . implode(' ', $packages);
    } elseif(executeCommand('which yum')) {
        $installCmd = 'yum install -y ' . implode(' ', $packages);
    } elseif(executeCommand('which pacman')) {
        $installCmd = 'pacman -S --noconfirm ' . implode(' ', $packages);
    } elseif(executeCommand('which brew')) {
        $installCmd = 'brew install ' . implode(' ', $packages);
    } else {
        return false;
    }

    exec($installCmd . ' 2>/dev/null', $output, $returnCode);
    return $returnCode === 0;
}

function getStoragePath($selectedStorage): string {
    switch($selectedStorage[0]) {
        case 1:
            return '/mnt/axiom-os';
        case 2:
            echo "\033[38;5;208mEnter custom path: \033[0m";
            system('stty -cbreak echo');
            $customPath = trim(fgets(STDIN));
            system('stty cbreak -echo');
            return $customPath ?: '/opt/axiom-os';
        default:
            return '/opt/axiom-os';
    }
}

/** @noinspection PhpUnusedParameterInspection */
function install($selectedComponents, $storagePath, $startWithServer): bool {
    $repoUrl = 'https://github.com/thoq-jar/axiom-os.git';
    $serviceName = 'axiom-os';

    $tasks = [
        'Checking system dependencies',
        'Installing missing dependencies',
        'Cloning repository',
        'Building project',
        'Creating launch script'
    ];

    if($startWithServer) {
        $tasks[] = 'Creating startup script';
    }

    $tasks[] = 'Finalizing installation';

    $totalTasks = count($tasks);

    echo "\n\033[38;5;214mStarting Axiom Installation...\033[0m\n\n";

    showProgress($tasks[0], 1, $totalTasks);
    $deps = checkDependencies();
    $missingRequired = array_filter($deps, function($dep) {
        return $dep['required'] && !$dep['available'];
    });

    if(!empty($missingRequired)) {
        echo "\n\033[38;5;196mMissing required dependencies:\033[0m\n";
        foreach($missingRequired as $info) {
            echo "  ✗ {$info['name']}\n";
        }

        showProgress($tasks[1], 2, $totalTasks);
        if(!installDependencies()) {
            echo "\n\033[38;5;196mFailed to install dependencies\033[0m\n";
            return false;
        }
    }

    showProgress($tasks[2], 3, $totalTasks);
    if(!cloneRepository($repoUrl, $storagePath)) {
        echo "\n\033[38;5;196mFailed to clone repository\033[0m\n";
        return false;
    }

    showProgress($tasks[3], 4, $totalTasks);
    if(!buildProject($storagePath)) {
        echo "\n\033[38;5;196mFailed to build project\033[0m\n";
        return false;
    }

    showProgress($tasks[4], 5, $totalTasks);
    if(!createLaunchScript($storagePath)) {
        echo "\n\033[38;5;196mFailed to create launch script\033[0m\n";
        return false;
    }

    $currentTask = 6;

    if($startWithServer) {
        showProgress($tasks[5], $currentTask, $totalTasks);
        if(!createStartupScript($storagePath, $serviceName)) {
            echo "\n\033[38;5;196mFailed to create startup script\033[0m\n";
            return false;
        }

        exec("systemctl start $serviceName", $output, $returnCode);
        if($returnCode !== 0) {
            echo "\n\033[38;5;196mWarning: Failed to start service\033[0m\n";
        }
        $currentTask++;
    }

    showProgress($tasks[count($tasks) - 1], $currentTask, $totalTasks);

    return true;
}

function main(): void {
    system('clear');
    showAsciiArt();

    echo "\033[38;5;214mWelcome to Axiom Homelab Server Installation\033[0m\n\n";
    echo "Press ENTER to continue or Q to quit...\n";

    $key = readKey();
    if($key === 'q' || $key === 'Q') {
        echo "\033[38;5;208mInstallation cancelled.\033[0m\n";
        exit(0);
    }

    showSpinner("Loading components...", function() {
        usleep(1000000);
        return true;
    });

    $components = [
        "Axiom Autostart (Start with server)",
        "Container Runtime (Docker)",
        "Monitoring Stack (Grafana)",
    ];

    $selectedComponents = getUserSelection("Select Components to Install:", $components);

    $storageOptions = [
        "Local Storage (/opt/axiom-os)",
        "External Drive (/mnt/axiom-os)",
        "Custom Path"
    ];

    $selectedStorage = getUserSelection("Select Storage Location:", $storageOptions, false);
    $storagePath = getStoragePath($selectedStorage);

    $startWithServer = in_array(0, $selectedComponents);

    system('clear');
    showAsciiArt();

    echo "\033[38;5;214mInstallation Configuration:\033[0m\n\n";

    echo "\033[38;5;208mComponents:\033[0m\n";
    foreach($selectedComponents as $index) {
        echo "  ● $components[$index]\n";
    }

    echo "\033[38;5;208mStorage:\033[0m $storagePath\n";
    echo "\033[38;5;208mStart with server:\033[0m " . ($startWithServer ? 'Yes' : 'No') . "\n\n";

    echo "Press ENTER to begin installation or Q to quit...\n";

    $key = readKey();
    if($key === 'q' || $key === 'Q') {
        echo "\033[38;5;208mInstallation cancelled.\033[0m\n";
        exit(0);
    }

    $success = install($selectedComponents, $storagePath, $startWithServer);

    if($success) {
        echo "\n\n\033[38;5;214m✓ Installation completed successfully!\033[0m\n";
        echo "\033[38;5;208mAxiom Homelab Server is now ready.\033[0m\n";
        if($startWithServer) {
            echo "\033[38;5;208mService started and enabled for boot.\033[0m\n";
        }
    } else {
        echo "\n\n\033[38;5;196m✗ Installation failed!\033[0m\n";
        echo "\033[38;5;208mPlease check the errors above and try again.\033[0m\n";
    }
}

if(php_sapi_name() === 'cli') {
    main();
}
