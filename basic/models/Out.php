<?php
namespace app\models;

use Yii;
use yii\base\Model;
use yii\base\Exception;

class YouTubeAPI extends Model
{

    private static $CREDENTIALS_PATH = '~/php-yt-oauth2.json';

    private $client;
    private $service;
    
    /*
     * getters and private vars shall be later...
     */
    public $url;
    public $videoID;
    public $channelID;
    public $q;
    public $maxResults;
    public $locationCode;
    public $nextPageToken;

    function __construct()
    {
        // Define an object that will be used to make all API requests.
        
        try {
        $tmpclnt = $this->getClient();
        } catch (\Exception $e) {
            $tmpclnt = $this->client;
        }

        $this->client = $tmpclnt;
        $this->service = new Google_Service_YouTube($this->client);
    }

    function getVideoStats()
    {
        if ($this->videoID) {
            return $this->getVideoStatById($this->service, 'snippet,contentDetails,statistics', array(
                'id' => $this->videoID,
                '' => ''
            ));
        } else {
            return null;
        }
    }

    function getVideoSearch()
    {
        if ($this->channelID) {
            return $this->getVideoStatBySearch($this->service, 'snippet', array(
                'q' => $this->q,
                'maxResults' => $this->maxResults,
                'regionCode' => $this->locationCode,
                'type' => 'video',
                'pageToken' => $this->nextPageToken
                //'order' => 'rating'
            ));
        } else {
            return null;
        }
    }

    function setURL($url)
    {
        $this->url = $url;
        parse_str(parse_url($url)['query'], $url);
        $this->videoID = $url['v'];
    }

    function setChannelID($id)
    {
        $this->channelID = $id;
    }
    
    function setQ($q)
    {
        $this->q = $q;
    }
    
    function setLocationCode($lc)
    {
        $this->locationCode = $lc;
    }
    
    function setMaxResults($mr)
    {
        $this->maxResults = $mr;
    }

    function getClient()
    {
        $client = new Google_Client();
        // Set to name/location of your client_secrets.json file.
        $client->setAuthConfigFile('client_secrets.json');
        // Set to valid redirect URI for your project.
        $client->setRedirectUri('http://localhost');
        
        $client->addScope(Google_Service_YouTube::YOUTUBE_READONLY);
        $client->setAccessType('offline');
        
        // Load previously authorized credentials from a file.
        $credentialsPath = $this->expandHomeDirectory($this::$CREDENTIALS_PATH);
        if (file_exists($credentialsPath)) {
            $accessToken = file_get_contents($credentialsPath);
        } else {
            
            var_dump($credentialsPath);
            
            exit();
        }
        $client->setAccessToken($accessToken);
        
        // Refresh the token if it's expired.
        if ($client->isAccessTokenExpired()) {
            $client->refreshToken($client->getRefreshToken());
            file_put_contents($credentialsPath, $client->getAccessToken());
        }
        return $client;
    }

    /**
     * Expands the home directory alias '~' to the full path.
     *
     * @param string $path
     *            the path to expand.
     * @return string the expanded path.
     */
    function expandHomeDirectory($path)
    {
        $homeDirectory = getenv('HOME');
        if (empty($homeDirectory)) {
            $homeDirectory = getenv("HOMEDRIVE") . getenv("HOMEPATH");
        }
        return str_replace('~', realpath($homeDirectory), $path);
    }

    // Call videos.list to retrieve information
    function getVideoStatById($service, $part, $params)
    {
        $params = array_filter($params);
        $response = $service->videos->listVideos($part, $params);
        return $response;
    }
    
    // Call search.list to retrieve information
    function getVideoStatBySearch($service, $part, $params)
    {
        $params = array_filter($params);
        $response = $service->search->listSearch($part, $params);
        return $response;
    }
    
}

?>