module Api
  class GeolocationController < ApplicationController
    def fetch
      require 'net/http'
      require 'json'

      uri = URI('http://ip-api.com/json')
      response = Net::HTTP.get_response(uri)
      
      if response.is_a?(Net::HTTPSuccess)
        render json: JSON.parse(response.body)
      else
        render json: { error: 'Failed to fetch geolocation' }, status: 500
      end
    end
  end
end
