module Api
  class AppstoreController < ApplicationController
    protect_from_forgery with: :null_session
    skip_before_action :verify_authenticity_token

    APPS_FILE = Rails.root.join('config', 'installed_apps.json')

    def install
      app = params[:app]
      
      # Load or initialize installed apps
      installed_apps = load_installed_apps
      installed_apps[app] = {
        installed_at: Time.current,
        status: 'active'
      }
      
      # Save to file
      save_installed_apps(installed_apps)
      
      render json: { success: true }
    rescue => e
      render json: { success: false, error: e.message }, status: :unprocessable_entity
    end

    def uninstall
      app = params[:app]
      
      installed_apps = load_installed_apps
      installed_apps.delete(app)
      
      save_installed_apps(installed_apps)
      
      render json: { success: true }
    rescue => e
      render json: { success: false, error: e.message }, status: :unprocessable_entity
    end

    def status
      app = params[:app]
      installed_apps = load_installed_apps
      
      render json: {
        installed: installed_apps.key?(app),
        info: installed_apps[app]
      }
    end

    private

    def load_installed_apps
      if File.exist?(APPS_FILE)
        JSON.parse(File.read(APPS_FILE))
      else
        {}
      end
    end

    def save_installed_apps(apps)
      File.write(APPS_FILE, JSON.pretty_generate(apps))
    end
  end
end