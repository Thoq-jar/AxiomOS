docker_compose = <<~DOCKER_COMPOSE
services:
  pihole:
    container_name: pihole
    image: pihole/pihole:latest
    ports:
      - "53:53/tcp"
      - "53:53/udp"
      - "67:67/udp"
      - "80:80/tcp"
    environment:
      TZ: 'America/Chicago'
      WEBPASSWORD: 'admin'
    volumes:
      - './etc-pihole:/etc/pihole'
      - './etc-dnsmasq.d:/etc/dnsmasq.d'
    cap_add:
      - NET_ADMIN
    restart: unless-stopped
DOCKER_COMPOSE

module Api
  class AppstoreController < ApplicationController
    protect_from_forgery with: :null_session
    skip_before_action :verify_authenticity_token

    APPS_FILE = Rails.root.join("config", "installed_apps.json")

    def install
      app = params[:app]
      logs = []
      installed_apps = load_installed_apps

      begin
        # Run the installation command based on app type

        # Skip install for now
        render json: { success: true }
        installed_apps[app] = {
            installed_at: Time.current,
            status: "active"
        }

        save_installed_apps(installed_apps)
        render json: {
          success: true,
          logs: logs
        }
        return

        ## WONT BE CALLED ##
        success, install_logs = send("install_#{app}")
        logs.concat(install_logs) if install_logs

        if success
          installed_apps[app] = {
            installed_at: Time.current,
            status: "active"
          }

          save_installed_apps(installed_apps)
          render json: {
            success: true,
            logs: logs
          }
        else
          render json: {
            success: false,
            error: "Installation failed - one or more subcommands failed",
            logs: logs
          }, status: :internal_server_error
        end

      rescue => error
        render json: {
          success: false,
          error: "Installation failed - #{error.message}",
          logs: logs
        }, status: :internal_server_error
      end
    end

    def uninstall
      app = params[:app]
      begin
        installed_apps = load_installed_apps
        installed_apps.delete(app)
        save_installed_apps(installed_apps)
        render json: { success: true }
      rescue => error
        render json: {
          success: false,
          error: "Uninstall failed - #{error.message}"
        }, status: :internal_server_error
      end
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

    def install_pihole
      logs = []
      logs << "Received command to install pihole"

      logs << "Checking for docker"
      docker = system("docker --help", out: logs, err: logs)
      docker_compose = system("docker compose --help", out: logs, err: logs)

      if docker or docker_compose == false
        logs << "Docker or docker-compose not found, installing"
        docker_install = system("curl -sSL https://get.docker.com | sh", out: logs, err: logs)
        return [false, logs] unless docker_install

        logs << "Setting up docker"
        docker_setup = system("sudo usermod -aG docker $USER", out: logs, err: logs)
        return [false, logs] unless docker_setup
      end

      logs << "Setting up Pi-hole"
      setup = system("sudo mkdir -p /opt/stacks/pihole", out: logs, err: logs)
      return [false, logs] unless setup

      [true, logs]
    end

    def render_error(message, logs)
      render json: {
        success: false,
        error: message,
        logs: logs
      }, status: :internal_server_error
    end
  end
end
