class WidgetsController < ApplicationController
  def show
    render partial: "home/ui/widgets/apps/#{params[:name]}"
  rescue ActionView::MissingTemplate
    render plain: "Widget not found", status: :not_found
  end
end