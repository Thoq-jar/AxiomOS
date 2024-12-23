module Api
  class CommandsController < ApplicationController
    protect_from_forgery with: :null_session

    def execute
      command = params[:command].to_s.strip
      cmd_parts = command.split(" ")
      base_cmd = cmd_parts[0]
      arguments = cmd_parts[1..]&.join(" ")

      allowed_commands = {
        "date" => [],
        "pwd" => [],
        "whoami" => [],
        "ls" => [ "-l", "-a", "-la", "-al" ],
        "echo" => :any,
        "hostname" => [],
        "uname" => [ "-a" ],
        "uptime" => [],
        "ps" => :any
      }

      if allowed_commands.key?(base_cmd)
        if arguments.present?
          if allowed_commands[base_cmd] == :any ||
             (allowed_commands[base_cmd].any? && allowed_commands[base_cmd].include?(arguments))
            output = execute_command(base_cmd, arguments)
            render json: { output: output }
          else
            render json: { error: "Arguments not allowed" }, status: :forbidden
          end
        else
          output = execute_command(base_cmd)
          render json: { output: output }
        end
      else
        render json: { error: "Command not allowed" }, status: :forbidden
      end
    rescue => e
      render json: { error: e.message }, status: :internal_server_error
    end

    private

    def execute_command(cmd, args = nil)
      command = [ cmd, args ].compact.join(" ")
      require "open3"
      stdout, stderr, status = Open3.capture3(command)
      status.success? ? stdout : stderr
    end
  end
end
