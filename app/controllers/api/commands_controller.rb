module Api
  class CommandsController < ApplicationController
    protect_from_forgery with: :null_session

    def execute
      command = params[:command]

      allowed_commands = [
        "date",
        "pwd",
        "whoami",
        "ls",
        "echo",
        "hostname",
        "uname",
        "uptime",
        "ps"
      ]

      if allowed_commands.include?(command.split.first)
        output = `#{command}`
        render json: { output: output }
      else
        render json: { error: "Command not allowed" }, status: :forbidden
      end
    rescue => e
      render json: { error: e.message }, status: :internal_server_error
    end
  end
end
