set :domain,    "mtbeta.iostudio.com"
set :deploy_to, "/ioTools"
set :user,      "root"

role :app, domain, :primary => true

