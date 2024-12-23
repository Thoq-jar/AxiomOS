Rails.application.routes.draw do
  get "up" => "rails/health#show", as: :rails_health_check
  root "application#index"

  post "/api/command", to: "api/commands#execute"
end
