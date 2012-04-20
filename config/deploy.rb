set :application,   "ioTools" 
set :scm,         :git
set :branch,      'master'
#set :scm,           :file
#set :git_enable_submodules,  1
#set :repository,  "git@bitbucket.org:iostudio/iotools.git"
set :repository,    "./"
#set :deploy_via,  :remote_cache
set :deploy_via,    :copy
set :copy_strategy, :export
set :use_sudo,      false
set :keep_releases, 2

ssh_options[:forward_agent] = true

desc "Composer Goodness"
namespace :composer do
  desc "Install composer on the server"
  task :install_composer do
    run "cd #{shared_path}; curl -s http://getcomposer.org/installer | php"
  end

  desc "Update Composer"
  task :self_update do
    run "cd #{shared_path}; ./composer.phar self-update"
  end

  desc "Install Vendors"
  task :install do
    run "cd #{current_release}; ./composer.phar install"
  end

  desc "Update Vendors"
  task :update do
    run "cd #{current_release}; ./composer.phar update"
  end
end

=begin
This will take care of installing composer and downloading the vendors
=end
after("deploy:setup") do
  composer.install_composer
end

=begin
This will create the symlinks that we need
=end
before("deploy:finalize_update") do
  run "if [ -f #{deploy_to}/#{shared_dir}/composer.phar ]; then ln -sf #{deploy_to}/#{shared_dir}/composer.phar #{current_release}/composer.phar; fi";
  run "if [ -d #{deploy_to}/#{shared_dir}/vendor ]; then ln -sf #{deploy_to}/#{shared_dir}/vendor #{current_release}/vendor; fi"
  composer.update
  deploy.cleanup
end
