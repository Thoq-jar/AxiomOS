require_relative "boot"

require "rails"

require "active_model/railtie"
require "action_controller/railtie"
require "action_view/railtie"
require "rails/test_unit/railtie"

Bundler.require(*Rails.groups)

module AxiomOs
  class Application < Rails::Application
    config.load_defaults 8.0
    config.assets.css_compressor = nil
    config.autoload_lib(ignore: %w[assets tasks])
    config.generators.system_tests = nil
  end
end
