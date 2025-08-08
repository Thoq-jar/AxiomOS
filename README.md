# Axiom-A modern homelab management tool

# Installation
Prerequisites:
- PHP 8.4+
- UNIX (No support will be provided for Windows)

### Install PHP and Node
Linux:
```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/linux)"
```

```sh
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
\. "$HOME/.nvm/nvm.sh"
nvm install 22
```

Now create these syslinks:
```sh
sudo ln ~/.config/herd-lite/bin/php /usr/local/bin/php
sudo ln ~/.config/herd-lite/bin/composer /usr/local/bin/composer
sudo ln ~/.config/herd-lite/bin/laravel /usr/local/bin/laravel
sudo ln -sf ~/.nvm/versions/node/$(ls ~/.nvm/versions/node/ | grep -E '^v[0-9]+\.[0-9]+\.[0-9]+$' | sort -V | tail -1)/bin/node /usr/local/bin/node
sudo ln -sf ~/.nvm/versions/node/$(ls ~/.nvm/versions/node/ | grep -E '^v[0-9]+\.[0-9]+\.[0-9]+$' | sort -V | tail -1)/bin/npm /usr/local/bin/npm
```

macOS:
```sh
/bin/bash -c "$(curl -fsSL https://php.new/install/mac)" 
 ```

```sh
curl -o- https://raw.githubusercontent.com/nvm-sh/nvm/v0.40.3/install.sh | bash
\. "$HOME/.nvm/nvm.sh"
nvm install 22
```

### Install Axiom
```shell
curl -fsSL https://raw.githubusercontent.com/Thoq-jar/AxiomOS/refs/heads/main/install.php > /tmp/axiomup && sudo php /tmp/axiomup
```

# License
This project uses the MIT license,
see the [LICENSE](LICENSE.md) for more details.
