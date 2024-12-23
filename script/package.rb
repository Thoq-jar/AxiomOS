#!/usr/bin/env ruby

def directory_exists?(directory)
  File.directory?(directory)
end

def command_exists?(command)
  system("which #{command} > /dev/null 2>&1")
end

def create_directory(directory)
  Dir.mkdir(directory)
end

def log(message)
  puts "[Radii Bundler] #{message}"
end

def cd(directory)
  Dir.chdir(directory)
end

def main
  log("Welcome to AxiomOS!")

  if !command_exists?("docker")
    log("Docker is not installed. Please install Docker.")
    exit 1
  end

  if !command_exists?("bundle")
    log("Bundler is not installed. Please install Bundler.")
    exit 1
  end

  if !directory_exists?("build")
      create_directory("build")
  end

  log("Installing deps...")
  system("bundle install")

  log("Building Docker image...")
  system("docker build -t axiom_os .")

  log("Entering \"build\" directory...")
  cd("build")

  log("Creating self-contained tarball...")
  system("docker save axiom_os > axiom_os.tar")

  log("Leaving \"build\" directory...")
  cd("..")

  log("Setting up start script...")
  system("chmod +x script/start.rb")

  log("Done!")
  puts "To run: ./script/start.rb"
  exit 0
end

main if __FILE__ == $PROGRAM_NAME
