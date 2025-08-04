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

function showProgress($message, $percentage): void {
    $barLength = 50;
    $filled = intval($percentage * $barLength / 100);
    $empty = $barLength - $filled;

    $bar = str_repeat("█", $filled) . str_repeat("░", $empty);

    echo "\r\033[38;5;208m$message\033[0m [\033[38;5;214m$bar\033[0m] $percentage%";
    flush();
}

function install() {}

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

    $components = [
        "Web Dashboard",
        "Container Runtime (Docker)",
        "Monitoring Stack (Grafana/Prometheus)",
    ];

    $selectedComponents = getUserSelection("Select Components to Install:", $components);

    $environments = [
        "Development",
        "Production",
        "Testing"
    ];

    $selectedEnv = getUserSelection("Select Environment:", $environments, false);

    $storageOptions = [
        "Local Storage (/opt/axiom)",
        "External Drive (/mnt/axiom)",
        "Network Storage (NFS)",
        "Custom Path"
    ];

    $selectedStorage = getUserSelection("Select Storage Location:", $storageOptions, false);

    system('clear');
    showAsciiArt();

    echo "\033[38;5;214mInstallation Configuration:\033[0m\n\n";

    echo "\033[38;5;208mComponents:\033[0m\n";
    foreach($selectedComponents as $index) {
        echo "  ● $components[$index]\n";
    }

    echo "\n\033[38;5;208mEnvironment:\033[0m {$environments[$selectedEnv[0]]}\n";
    echo "\033[38;5;208mStorage:\033[0m {$storageOptions[$selectedStorage[0]]}\n\n";

    echo "Press ENTER to begin installation or Q to quit...\n";

    $key = readKey();
    if($key === 'q' || $key === 'Q') {
        echo "\033[38;5;208mInstallation cancelled.\033[0m\n";
        exit(0);
    }

    echo "\n\033[38;5;214mStarting Axiom Installation...\033[0m\n\n";

    for($i = 0; $i <= 100; $i += 5) {
        showProgress("Installing components", $i);
        usleep(200000);
    }

    echo "\n\n\033[38;5;214m✓ Installation completed successfully!\033[0m\n";
    echo "\033[38;5;208mAxiom Homelab Server is now ready.\033[0m\n\n";

    install();
}

if(php_sapi_name() === 'cli') {
    main();
}

