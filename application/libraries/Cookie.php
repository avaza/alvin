<?php

class Cookie {

    protected $string;
    protected $cookie;
    protected $tokens;
    protected $result;
    protected $contnt;

    /**
     * Expects strings containing the filename and path of a cookie file.
     * @param string $file The filename of the cookie file.
     * @param string $path The relative path of the cookie file.
     *
     * @return array The array of cookies as extracted from the string.
     */
    public function extract($file, $path = '/')
    {
        //Extract the contents of the cookie file
        $this->contnt = file($path . $file);
        if(!$this->contnt)
        {
            die('Bad File');
        }

        //Parse the file contents to retrieve cookie values
        Cookie::extractCookies();

        return $this->result;
    }

    /**
     * Expects $this->contnt to be a string containing the contents of a cookie file.
     *
     * @return array The array of cookies as extracted from the string.
     */
    protected function extractCookies()
    {
        // parse each line and extract token values
        foreach ($lines as $line)
        {
            $this->string = preg_replace('/([\r\n\t])/','', $line);

            // detect #HttpOnly cookies and remove prefix
            Cookie::parseHttpOnly();

            // strip and trim string to retain valuable definitions
            Cookie::parseCookieString();

            if(!$this->tokens)
            {
                die('Invalid Cookie');
            }

            // Extract the data
            Cookie::extractResult();
        }

        die(json_encode($this->result));

        return $this;
    }

    /**
     * Check line of cookie string for #HttpOnly.
     *
     * @return boolean true if the cookie IS #HttpOnly.
     */
    protected function isHttpOnly()
    {
        return substr($this->string, 0, 10) == '#HttpOnly_';
    }

    /**
     * Detects if the current line of the cookie contains #HttpOnly.
     *
     * Sets $line and $cookie values according to the results of check.
     */
    protected function parseHttpOnly()
    {
        $this->cookie = [];
        if(Cookie::isHttpOnly())
        {
            $this->string = substr($this->string, 10);
            $this->cookie['http_only'] = true;
        }
        else
        {
            $this->cookie['http_only'] = false;
        }

        return $this;
    }

    /**
     * Check line of cookie string contains valuable definitions.
     *
     * @return boolean true if the cookie HAS valuable definitions.
     */
    protected function hasValuableDefinitions()
    {
        return $this->string[0] != '#' && substr_count($this->string, "\t") == 6;
    }

    /**
     * Detects if the current line of the cookie contains valuable definitions.
     *
     * Returns array containing valuable definitions (trimmed)
     */
    protected function parseCookieString()
    {
        $this->tokens = false;
        if(Cookie::hasValuableDefinitions())
        {
            // get tokens in an array
            $this->tokens = explode("\t", $this->string);

            // trim the tokens
            $this->tokens = array_map('trim', $this->tokens);
        }

        return $this;
    }

    /**
    * Extracts a definition contained within the cookie.
    *
    * Returns array containing extracted definition
    */
    protected function extractResult()
    {
        $this->cookie['domain'] = $this->tokens[0]; // The domain that created AND can read the variable.
        $this->cookie['flag'] = $this->tokens[1];   // A TRUE/FALSE value indicating if all machines within a given domain can access the variable.
        $this->cookie['path'] = $this->tokens[2];   // The path within the domain that the variable is valid for.
        $this->cookie['secure'] = $this->tokens[3]; // A TRUE/FALSE value indicating if a secure connection with the domain is needed to access the variable.
        $this->cookie['expiration-epoch'] = $this->tokens[4];  // The UNIX time that the variable will expire on.
        $this->cookie['name'] = urldecode($this->tokens[5]);   // The name of the variable.
        $this->cookie['value'] = urldecode($this->tokens[6]);  // The value of the variable.
        $this->cookie['expiration'] = date('Y-m-d h:i:s', $this->tokens[4]); // Date converted to a readable format

        // Record the cookie.
        $this->result[] = $this->cookie;

        return $this;
    }
}
