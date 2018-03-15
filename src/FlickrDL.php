<?php
/**
 *  @file FlickrDL.php
 *  Get the largest photo from Flickr URL.
 */
namespace f2face\FlickrDL;

class FlickrDL
{
    protected $api_key;
    
    private $api_endpoint = 'https://api.flickr.com/services/rest/?method=flickr.photos.getSizes&format=json&nojsoncallback=1&api_key=%s&photo_id=%s';
    
    public function __construct($api_key = null)
    {
        $this->api_key = $api_key;
    }
    
    public function setApiKey(string $api_key)
    {
        $this->api_key = $api_key;
        return $this;
    }
    
    public function getApiKey()
    {
        return $this->api_key;
    }
    
    public function getBest(string $url)
    {
        $sizes = $this->retrieveSizes($url);
        return end($sizes);
    }
    
    public function getSquare(string $url)
    {
        $sizes = $this->retrieveSizes($url);
        return $sizes[0];
    }
    
    public function refreshApiKey()
    {
        // Get the latest API key from API Explorer
        $flickr_api_explorer = 'https://www.flickr.com/services/api/explore/flickr.photos.getSizes';
        $data = file_get_contents($flickr_api_explorer);
        preg_match('#"api_key":"(\w+)"#i', $data, $match);
        
        if (empty($match[1]))
            throw new \Exception('Failed refreshing the API key.');
        
        $this->api_key = $match[1];
        return $match[1];
    }
    
    protected function retrieveSizes(string $url)
    {
        $api = sprintf($this->api_endpoint, $this->api_key, $this->getFileIdFromUrl($url));
        
        $obj = $this->getResourceAndSanitizeData($api);
        
        if (empty($obj->sizes->size))
            throw new \Exception('Photo not found', 404);
        
        return $obj->sizes->size;
    }
    
    protected function getFileIdFromUrl(string $url)
    {
        if (!$this->isValidFlickrUrl($url))
            throw new \Exception('URL is not valid.', 404);
        
        preg_match('#/(\d+)/?#', $url, $match);
        return $match[1];
    }
    
    protected function isValidFlickrUrl(string $url)
    {
        return (bool) preg_match('#^https?://([w\-]+\.)?flickr.com/photos/[^/]+/\d+#i', $url);
    }
    
    protected function getResourceAndSanitizeData(string $api_url)
    {
        $data = NULL;
        if (function_exists('file_get_contents'))
            $data = file_get_contents($api_url);
        
        $obj = json_decode($data);
        
        if (!$obj)
            throw new \Exception('Error parsing data.', 500);
        
        return $obj;
    }
}