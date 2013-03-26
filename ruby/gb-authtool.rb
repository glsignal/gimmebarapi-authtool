require 'yaml'
require 'json'
require 'rest-client'

# Include indifferent_access for some hash niceties
require 'active_support/core_ext/hash/indifferent_access'

# Load in the configuration file
if File.exist? 'config.yml'
  config = YAML.load_file('config.yml').with_indifferent_access
else
  puts "config.yml not found. Please copy the example file and modify."
  exit
end



#
# We start off by obtaining a request token from the Gimme Bar API
#
begin
  response = RestClient.post(
    "#{config[:api_base_url]}auth/token/request",
    :client_id      => config[:client_id],
    :client_secret  => config[:client_secret],
    :type           => 'app'
  )
  response = JSON.parse(response).with_indifferent_access
rescue => e
  puts "Error getting request token."
  puts e.response
  exit
end



#
# Since we now have a request token, we need the account holder to approve it.
#
request_token = response[:request_token]
url = "#{config[:site_base_url]}authorize?client_id=#{config[:client_id]}&token=#{request_token}&response_type=code"

puts <<EOF
Successfully obtained request_token.
Please visit the following URL in your web browser and return here when you're finished.


#{url}


EOF

print "Press Enter to resume..."
gets # Pause execution until they return



#
# Assuming the token has been approved by the account holder, we now exchange
# the request token for an authorization token.
#
begin
  response = RestClient.post(
    "#{config[:api_base_url]}auth/token/authorization",
    :client_id      => config[:client_id],
    :token          => request_token,
    :response_type  => :code
  )
  response = JSON.parse(response).with_indifferent_access
rescue => e
  puts "Error getting authorization token."
  puts e.response
  exit
end



#
# Now that we have the authorization token, we need to exchange that with the
# Gimmebar server for an access token.
#
begin
  response = RestClient.post(
    "#{config[:api_base_url]}auth/token/access",
    :code           => response[:code],
    :grant_type     => :authorization_code
  )
  response = JSON.parse(response).with_indifferent_access
rescue => e
  puts "Error getting access token."
  puts e.response
  exit
end



#
# We've successfully completed the authentication process.
# Let's output the result of our efforts.
#
puts <<EOF
Access token retrieved!

Username:     #{response[:username]}
User ID:      #{response[:user_id]}

Access Token: #{response[:access_token]}
EOF
