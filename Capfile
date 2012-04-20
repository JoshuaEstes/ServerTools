load 'deploy' if respond_to?(:namespace) # cap2 differentiator
Dir['plugins/*/lib/recipes/*.rb'].each { |plugin| load(plugin) }
Dir['vendor/plugins/*/recipes/*.rb'].each { |plugin| load(plugin) }
require 'rubygems'
require 'railsless-deploy'
load    'config/deploy'

# Load in the multistage configuration and setup the stages
set :stages, %w(mt mtbeta)
require 'capistrano/ext/multistage'

set :shared_children, %w(vendor)
set :shared_files,    %w(composer.phar)
