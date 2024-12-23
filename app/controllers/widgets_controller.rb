class WidgetsController < ApplicationController
  ALLOWED_WIDGETS = %w[pihole].freeze

  def show
    if ALLOWED_WIDGETS.include?(params[:name])
      render partial: "home/ui/widgets/apps/#{params[:name]}"
    else
      render plain: "Widget not found", status: :not_found
    end
  rescue ActionView::MissingTemplate
    render plain: "Widget not found", status: :not_found
  end
end
