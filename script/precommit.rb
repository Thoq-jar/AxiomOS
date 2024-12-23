#!/usr/bin/env ruby

puts "[PRE] Running pre-commit hooks..."

puts "[PRE] Running Brakeman..."
system("bin/brakeman --no-pager")

puts "[PRE] Running RuboCop..."
system("bin/rubocop -f github")
