# Axiom-A modern homelab management tool

# Installation
Prerequisites:
- PHP 8.4+
- UNIX (No support will be provided for Windows)

### Install PHP
Linux:
```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux)"
```

Now create these syslinks:
```sh
sudo ln ~/.config/herd-lite/bin/php /usr/local/bin/php
sudo ln ~/.config/herd-lite/bin/composer /usr/local/bin/composer
sudo ln ~/.config/herd-lite/bin/laravel /usr/local/bin/laravel
```

macOS:
```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac)" 
 ```

### Install Axiom
```shell
curl -fsSL https://raw.githubusercontent.com/Thoq-jar/AxiomOS/refs/heads/main/install.php > /tmp/axiomup && sudo php /tmp/axiomup
```

# License
This project uses the MIT license,
see the [LICENSE](LICENSE.md) for more details.
