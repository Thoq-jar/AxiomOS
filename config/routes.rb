Rails.application.routes.draw do
  get "up" => "rails/health#show", as: :rails_health_check
  root "application#index"

  post "/api/command", to: "api/commands#execute"
  get "/api/geolocation", to: "api/geolocation#fetch"
  post "/api/appstore", to: "api/appstore#modify"
  post "/api/appstore/install", to: "api/appstore#install"
  post "/api/appstore/uninstall", to: "api/appstore#uninstall"
  get "/widgets/apps/:name", to: "widgets#show"

  namespace :api do
    get "appstore/status", to: "appstore#status"
    post "appstore", to: "appstore#install"
  end
end
