<?php
/**
 * @file plugins/generic/socialMedia/plugins/twitter/classes/TwitterMessagePoster.inc.php
 *
 * Copyright (c) 2019 Centrum für Nah- und Mittelost-Studien (CNMS), Philipps Universität Marburg
 * Distributed under the GNU GPL v2. For full terms see the file LICENSE.
 *
 * @class TwitterMessagePoster
 * @ingroup plugins_generic_socialMedia
 *
 * @brief Posts Tweets to the Twitter API
 */

import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterPostTweetRequest');
import('plugins.generic.socialMedia.plugins.twitter.classes.OAuthAccessToken');
import('plugins.generic.socialMedia.plugins.twitter.classes.TwitterConsumer');


class TwitterMessagePoster {
    private $accessToken;
    private $accessTokenSecret;
    private $consumerKey;
    private $consumerSecret;

    /**
     * Constructor
     */
    function __contsruct() {
    }


    /**
     * Add the settings provided by the PostingChannel
     *
     * @param PostingChannel $channel
     */
    public function addSettings($channel) {
        $this->accessToken = $channel->getData('accessToken');
        $this->accessTokenSecret = $channel->getData('accessTokenSecret');
        $this->consumerKey = $channel->getData('consumerKey');
        $this->consumerSecret = $channel->getData('consumerSecret');
    }


    /**
     * Post a message
     *
     * @param Message $message
     *
     */
    public function postMessage($message) {
        // Generate request from message
        $parameters = [
            "status" => $message->getValue()
        ];

        $request = new TwitterPostTweetRequest($this->getConsumer(), $this->getOAuthToken(), $parameters);

        // Make request
        return $this->makeRequest($request->getAuthorizationHeader(), $parameters);
    }


    /**
     * Make the http request
     *
     * @param string $authorization
     * @param array $postFields
     *
     * @return string
     */
    private function makeRequest($authorization, $postFields) {
        $options = $this->getCurlOptions();
        $options[CURLOPT_HTTPHEADER] = [$authorization, 'Expect:'];
        $options[CURLOPT_POSTFIELDS] = Util::getHttpQuery($postFields);


        $curl = curl_init();
        curl_setopt_array($curl, $options);

        $response = curl_exec($curl);

        if (curl_errno($curl) > 0) {
            // TODO: Add meaningfull error message
            error_log("curl connection failed.");
        }

        // Check if posting succeeded
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 200) {
            $responseJSON = json_decode($response, true);

            if ($responseJSON == null) {
                return false;
            }

            if (!array_key_exists("id_str", $responseJSON)) {
                return false;
            }

            return ['state' => 'success'];
        } else {
            error_log(sprintf("CURL HTTP STATUS CODE: %s", curl_getinfo($curl, CURLINFO_HTTP_CODE)));
            error_log(sprintf("response: %s", print_r($response, true)));
            return false;
        }
    }


    /**
     * Return the default options for curl
     *
     * @return array
     */
    private function getCurlOptions() {
        return [
            CURLOPT_URL => TWITTER_API_HOST . "/" . TWITTER_API_VERSION . "/statuses/update.json",
            CURLOPT_POST => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 10
        ];
    }


    /**
     * Get the access token for the request
     *
     * @return OAuthToken
     */
    private function getOAuthToken() {
        return new OAuthAccessToken($this->accessToken, $this->accessTokenSecret);
    }


    /**
     * Get the TwitterConsumer
     *
     * @return TwitterConsumer
     */
    private function getConsumer() {
        return new TwitterConsumer($this->consumerKey, $this->consumerSecret);
    }
}

?>
