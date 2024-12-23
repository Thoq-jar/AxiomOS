#!/usr/bin/env ruby

puts "Running pre-commit hooks..."
exec("bin/brakeman --no-pager")
exec("bin/rubocop -f github")
