#!/usr/bin/env ruby

def directory_exists?(directory)
  File.directory?(directory)
end

def file_exists?(file)
  File.file?(file)
end

def command_exists?(command)
  system("which #{command} > /dev/null 2>&1")
end

def log(message)
  puts "[AxiomOS] #{message}"
end

def cd(directory)
  Dir.chdir(directory)
end

def cleanup
  # TODO: Fix this
  # log("Saving container state...")
  # system("docker commit $(docker ps -q --filter ancestor=axiom_os) axiom_os")
  # system("docker save axiom_os > axiom_os.tar")

  # log("Stopping container...")
  # system("docker stop $(docker ps -q --filter ancestor=axiom_os)")
  # system("docker rm $(docker ps -aq --filter ancestor=axiom_os)")

  log("Cleanup complete")
end

def generate_master_key
  if !file_exists?("config/master.key")
    log("Generating master key...")
    system("rails credentials:edit")
    File.read("config/master.key")
  end
end

def main
  [ :INT, :TERM, :QUIT, :TSTP ].each do |signal|
    Signal.trap(signal) do
      puts "\n"
      log("Captured signal #{signal}")
      cleanup
      exit 0
    end
  end

  if !command_exists?("docker")
    log("Docker is not installed. Please install Docker.")
    exit 1
  end

  if !directory_exists?("build")
    log("Please run the 'package.rb' script first!")
  end

  if !command_exists?("openssl")
    log("OpenSSL is not installed. Please install OpenSSL.")
  end

  master_key = generate_master_key

  log("Loading AxiomOS files...")
  cd("build")

  log("Loading AxiomOS image...")
  system("docker load < axiom_os.tar")

  log("Booting AxiomOS...")
  system("docker run -p 80:80 \
    -e RAILS_MASTER_KEY=#{master_key} \
    -e SECRET_KEY_BASE=$(openssl rand -hex 64) \
    -e RAILS_ENV=production \
    axiom_os"
  )

  cleanup
  exit 0
end

main if __FILE__ == $PROGRAM_NAME
