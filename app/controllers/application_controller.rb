class ApplicationController < ActionController::Base
  allow_browser versions: :modern

  def index
    respond_to do |format|
      format.html
    end
  end
end
