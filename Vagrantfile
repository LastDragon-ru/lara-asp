# -*- mode: ruby -*-
# vi: set ft=ruby :

# Symlinks on Windows:
#
# The user who run vagrant must have permission to create symlinks, by default
# only Administrators can do that.
#
# https://superuser.com/questions/104845/permission-to-make-symbolic-links-in-windows-7

require 'json'
require 'yaml'
require 'digest/sha1'
require 'etc'

settings = File.exists?('Vagrant.yml') ? YAML.load_file('Vagrant.yml') : {};

Vagrant.configure(2) do |config|
  config.vm.box            = "ubuntu/jammy64"
  config.vm.hostname       = settings['host'] || getDefaultHost()
  config.vm.network       "private_network", type: "dhcp"
  config.vm.synced_folder ".", "/project", mount_options: ["dmode=0775,fmode=0775"]

  # Synced Folder
  if settings['smb']
    config.vm.synced_folder ".", "/project",
      type: "smb",
      smb_username: settings['smb']['username'],
      smb_password: settings['smb']['password'],
      mount_options: ["vers=default,mfsymlinks,dir_mode=0775,file_mode=0775"]
  end

  # Ssh
  config.vm.network        :forwarded_port, id: 'ssh', guest: 22, host: 2222, auto_correct: true
  config.ssh.forward_agent = true

  # Hosts
  config.hostmanager.enabled      = true
  config.hostmanager.manage_host  = true
  config.hostmanager.manage_guest = false
  config.vm.provision :hostmanager, run: 'always'

  # DHCP IP fix
  #
  # https://github.com/devopsgroup-io/vagrant-hostmanager/issues/86
  config.hostmanager.ip_resolver = proc do |vm, resolving_vm|
    getHostIp(vm)
  end

  # Required plugins
  config.vagrant.plugins = []
  config.vagrant.plugins << "vagrant-hostmanager"

  # VirtualBox
  config.vm.provider "virtualbox" do |v|
     v.name   = "vagrant##{config.vm.hostname}@" +  Digest::SHA1.hexdigest(__FILE__)
     v.cpus   = Etc.nprocessors
     v.memory = settings['vm'] && settings['vm'] ? settings['vm']['memory'] : "2048"
     v.customize ["modifyvm", :id, "--description", __dir__]
  end

  # Git
  if settings['git']
    gitConfig = ""

    settings['git'].each do | key, value |
      gitConfig += "git config --global #{key} \"#{value}\"\n"
    end

    config.vm.provision "git config", type: "shell", privileged: false, inline: gitConfig
  end

  # GnuPG
  config.vm.provision "gpg fix: inappropriate ioctl for device", type: "shell", privileged: false, inline: <<-SHELL
    if ! grep -q "export GPG_TTY" ~/.profile; then
      echo "export GPG_TTY=\\$(tty)" >> ~/.profile
    fi
  SHELL

  if settings['gpg']
    if settings['gpg']['keys']
      settings['gpg']['keys'].each do | key, path |
        if path
          config.vm.provision "file", source: path, destination: "~/gpg-#{key}-key.asc"
          config.vm.provision "gpg import #{key} key", type: "shell", privileged: false, inline: <<-SHELL
            gpg --batch --import ~/gpg-#{key}-key.asc
          SHELL
        end
      end
    end

    if settings['gpg']['forward'] && settings['gpg']['forward']['local']
      config.ssh.extra_args = [
        "-o", "RemoteForward=#{settings['gpg']['forward']['remote']} #{settings['gpg']['forward']['local']}",
        "-o", "StreamLocalBindUnlink=yes"
      ]
    end
  end

  # Provision
  config.vm.provision "os:update", type: "shell", privileged: false, inline: <<-SHELL
    sudo apt-get update
  SHELL

  config.vm.provision "os:motd", type: "shell", privileged: false, inline: <<-SHELL
    sudo chmod -x /etc/update-motd.d/*
    sudo tee /etc/update-motd.d/55-project > /dev/null <<"EOT"
#!/usr/bin/env sh
printf "      Host: http://$(hostname)/\n"
EOT
    sudo chmod u+x /etc/update-motd.d/00-header
    sudo chmod u+x /etc/update-motd.d/55-project
  SHELL

  config.vm.provision "os:hostname", type: "shell", privileged: false, run: 'always', inline: <<-SHELL
    sudo hostnamectl set-hostname #{config.vm.hostname}
  SHELL

  config.vm.provision "os:multipath-fix", type: "shell", privileged: false, inline: <<-SHELL
    # Fix for
    # sdb: failed to get udev uid: Invalid argument
    # sdb: failed to get unknown uid: Invalid argument
    # sdb: failed to get path uid

    sudo tee -a /etc/multipath.conf > /dev/null <<"EOT"
blacklist {
    device {
        vendor "VBOX"
        product "HARDDISK"
    }
}
EOT

    sudo systemctl restart multipathd
  SHELL

  config.vm.provision "user:profile", type: "shell", privileged: false, inline: <<-SHELL
    if ! grep -q "cd /project" ~/.profile; then
      echo "cd /project >& /dev/null" >> ~/.profile
    fi
  SHELL

  config.vm.provision "PHP 8.3", type: "shell", privileged: false, inline: <<-SHELL
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get install -y php8.3-{cli,common,mbstring,bcmath,zip,intl,mbstring,xml,xdebug,curl,pdo-sqlite}
    sudo sed -i 's/^error_reporting = .\+$/error_reporting = E_ALL/'            /etc/php/8.3/cli/php.ini
    sudo sed -i 's/^display_errors = .\+$/display_errors = On/'                 /etc/php/8.3/cli/php.ini
    sudo sed -i 's/^;opcache\.enable=.\+$/opcache.enable=1/'                    /etc/php/8.3/cli/php.ini
    sudo sed -i 's/^;opcache\.enable_cli=.\+$/opcache.enable_cli=1/'            /etc/php/8.3/cli/php.ini
    sudo tee -a /etc/php/8.3/mods-available/xdebug.ini > /dev/null <<"EOT"
xdebug.output_dir = /project/.xdebug
xdebug.profiler_output_name = callgrind.out.%t.%r
xdebug.client_host = 10.0.2.2
xdebug.mode = debug
xdebug.start_with_request = trigger
EOT
  SHELL

  config.vm.provision "composer", type: "shell", privileged: false, inline: <<-SHELL
    # Composer requires -H flag for all sudo commands
    #
    # See https://github.com/composer/composer/issues/6602
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo -H php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php

    if ! grep -q "COMPOSER_RUNTIME_ENV" /etc/environment; then
      sudo sh -c "echo "COMPOSER_RUNTIME_ENV=virtualbox" >> /etc/environment"
    fi
  SHELL

  config.vm.provision "composer install", type: "shell", privileged: false, inline: <<-SHELL
    if test -f "/project/composer.json"; then
      (cd /project && composer install)
      (cd /project && composer bin all install)
    fi
  SHELL

  config.vm.provision "npm", type: "shell", privileged: false, inline: <<-SHELL
    # See https://github.com/nodesource/distributions#debian-and-ubuntu-based-distributions
    sudo apt-get install -y ca-certificates curl gnupg
    sudo mkdir -p /etc/apt/keyrings
    curl -fsSL https://deb.nodesource.com/gpgkey/nodesource-repo.gpg.key | sudo gpg --dearmor -o /etc/apt/keyrings/nodesource.gpg

    NODE_MAJOR=20
    echo "deb [signed-by=/etc/apt/keyrings/nodesource.gpg] https://deb.nodesource.com/node_$NODE_MAJOR.x nodistro main" | sudo tee /etc/apt/sources.list.d/nodesource.list

    sudo apt-get update
    sudo apt-get install nodejs -y
  SHELL

  config.vm.provision "npm install", type: "shell", privileged: false, inline: <<-SHELL
    if test -f "/project/package.json"; then
      (cd /project && npm ci)
    fi
  SHELL
end

# Required to avoid error:
#
# A host only network interface you're attempting to configure via DHCP
# already has a conflicting host only adapter with DHCP enabled. The
# DHCP on this adapter is incompatible with the DHCP settings. Two
# host only network interfaces are not allowed to overlap, and each
# host only network interface can have only one DHCP server. Please
# reconfigure your host only network or remove the virtual machine
# using the other host only network.
#
# https://github.com/hashicorp/vagrant/issues/8878
#
# Seems will be fixed in v2.2.8
class VagrantPlugins::ProviderVirtualBox::Action::Network
  def dhcp_server_matches_config?(dhcp_server, config)
    true
  end
end

# Detect hostname
def getHostIp(machine)
  command =  "ip a | grep 'inet' | grep -v '127.0.0.1' | cut -d: -f2 | awk '{ print $2 }' | cut -f1 -d\"/\""
  result  = ""

  begin
    # sudo is needed for ifconfig
    machine.communicate.sudo(command) do |type, data|
      result << data if type == :stdout
    end
  rescue
    result = "# NOT-UP"
  end

  # the second inet is more accurate
  result.chomp.split("\n").last
end

def getDefaultHost()
  package   = getPackageName('./composer.json', getPackageName('./package.json', 'project'))
  host      = package.gsub(/^@?([^\/]+)\/(.+)$/, '\2.\1') + '.test'

  return host
end

def getPackageName(package, default = nil)
  return File.file?(package) ? JSON.parse(File.read(package))['name'] : default
end
