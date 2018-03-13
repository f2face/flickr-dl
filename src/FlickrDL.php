<?php
/**
 *  @file FlickrDL.php
 *  Get the largest photo from Flickr URL.
 */
namespace f2face\FlickrDL;

class FlickrDL
{
    protected $api_key;
    
    private $api_endpoint = 'https://www.flickr.com/services/api/render?method=flickr.photos.getSizes&api_key=%s&photo_id=%s&format=json&nojsoncallback=1';
    
    public function __construct($api_key = null)
    {
        $this->api_key = is_string($api_key) ? $api_key : '39bf0d19f3415af572548735d6b817e1';
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
        return end($this->retrieveSizes($url));
    }
    
    public function getSquare(string $url)
    {
        $sizes = $this->retrieveSizes($url);
        return $sizes[0];
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
        
        preg_match(
            '#<pre>(.+)</pre>#',
            preg_replace('#(\r|\n)#', '', $data),
            $match
        );
        
        $obj = json_decode($match[1]);
        
        if (!$obj)
            throw new \Exception('Error parsing data.', 500);
        
        return $obj;
    }
}