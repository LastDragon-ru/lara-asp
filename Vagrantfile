# -*- mode: ruby -*-
# vi: set ft=ruby :

# Symlinks on Windows:
#
# The user who run vagrant must have permission to create symlinks, by default
# only Administrators can do that.
#
# https://superuser.com/questions/104845/permission-to-make-symbolic-links-in-windows-7

require 'json'
require 'open3'
require 'digest/sha1'
require 'etc'

Vagrant.configure(2) do |config|
  config.vm.box            = "ubuntu/bionic64"
  config.vm.hostname       = getHostName()
  config.vm.network       "private_network", type: "dhcp"
  config.vm.synced_folder ".", "/project"

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
     v.memory = "2048"
     v.customize ["modifyvm", :id, "--description", __dir__]
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

  config.vm.provision "user:profile", type: "shell", privileged: false, inline: <<-SHELL
    echo "cd /project >& /dev/null" >> .profile
  SHELL

  config.vm.provision "PHP 8.0", type: "shell", privileged: false, inline: <<-SHELL
    sudo add-apt-repository -y ppa:ondrej/php
    sudo apt-get install -y php8.0-{cli,common,mbstring,bcmath,zip,intl,mbstring,xml,xdebug,curl,gd,imagick,ldap,pdo-sqlite}
    sudo sed -i 's/^error_reporting = .\+$/error_reporting = E_ALL/'            /etc/php/8.0/cli/php.ini
    sudo sed -i 's/^display_errors = .\+$/display_errors = On/'                 /etc/php/8.0/cli/php.ini
    sudo tee -a /etc/php/8.0/mods-available/xdebug.ini > /dev/null <<"EOT"
xdebug.remote_enable        = 1
xdebug.remote_host          = "10.0.2.2"
xdebug.profiler_output_dir  = "/project/.xdebug"
xdebug.profiler_output_name = "%u%R.cg"
EOT
  SHELL

  config.vm.provision "composer", type: "shell", privileged: false, inline: <<-SHELL
    # Composer requires -H flag for all sudo commands
    #
    # See https://github.com/composer/composer/issues/6602
    php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
    sudo -H php composer-setup.php --install-dir=/usr/local/bin --filename=composer
    rm composer-setup.php
  SHELL

  config.vm.provision "composer install", type: "shell", privileged: false, inline: <<-SHELL
    if test -f "/project/composer.json"; then
      (cd /project && composer install)
    fi
  SHELL

  config.vm.provision "npm", type: "shell", privileged: false, inline: <<-SHELL
    curl -fsSL https://deb.nodesource.com/setup_lts.x | sudo -E bash -
    sudo apt-get install -y nodejs
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

def getHostName()
  package   = getPackageName('./composer.json', getPackageName('./package.json', 'project'))
  host      = package.gsub(/^@?([^\/]+)\/(.+)$/, '\2.\1') + '.test'
  gitTag    = nil
  gitBranch = nil

  Open3.popen3("git rev-parse --abbrev-ref HEAD") do |stdin, stdout, stderr, wait_thr|
    gitBranch = stdout.read.strip()
  end

  if gitBranch != 'master'
    Open3.popen3("git describe --tags --exact-match") do |stdin, stdout, stderr, wait_thr|
      gitTag = stdout.read.strip()
    end

    if gitTag && gitTag != ''
      host = "v#{gitTag}.#{host}"
    elsif gitBranch && gitBranch != ''
      host = "#{gitBranch}.#{host}"
    end
  end

  return host
end

def getPackageName(package, default = nil)
  return File.file?(package) ? JSON.parse(File.read(package))['name'] : default
end
