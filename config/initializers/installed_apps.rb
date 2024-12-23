INSTALLED_APPS_FILE = Rails.root.join('config', 'installed_apps.json')

unless File.exist?(INSTALLED_APPS_FILE)
  File.write(INSTALLED_APPS_FILE, JSON.generate({}))
end