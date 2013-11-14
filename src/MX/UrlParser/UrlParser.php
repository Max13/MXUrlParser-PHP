<?php
namespace   MX\UrlParser;

/**
 * @brief       MXUrlParser
 *
 * @details     URL/URI parser
 *
 * @version     0.1.3
 * @author      Adnan "Max13" RIHAN <adnan@rihan.fr>
 * @link        http://rihan.fr/
 * @copyright   http://creativecommons.org/licenses/by-nc-sa/3.0/  CC-by-nc-sa 3.0
 *
 */

/**
 * Mozilla Public Suffix List
 */
define('MOZ_PSL', __DIR__.'/../../../data/mozilla_psl.txt');

class UrlParser
{
    /**
     * MXUrlParser Name
     */
    const NAME = 'MXUrlParser';

    /**
     * MXUrlParser Version
     */
    const VERSION = '0.1.3';

    /**
     * Complete URL/URI
     */
    private $m_url = null;

    /**
     * URL parts
     *
     * @var Associative array
     */
    private $m_urlParts = array(
        'scheme'    => null,
        'user'      => null,
        'pass'      => null,
        'host'      => null,
        'path'      => null,
        'query'     => null,
        'fragment'  => null,
        );

    /**
     * Host parts
     *
     * @var Associative array
     */
    private $m_hostParts = array(
        'subdomain' => null,
        'domain'    => null,
        'tld'       => null,
        );

    // -------------------- //

    /**
     * Constructor
     *
     * TODO: Add '*' parsing
     *
     * @param[in]   $_url     The URL/URI to be passed
     */
    public function __construct($_url)
    {
        // Check if public-suffix-list is available
        if (!is_readable(MOZ_PSL)) {
            throw new \Exception("Missing Mozilla PSL... Please run: ./bin/mx_psl.bash");
        }
        // ---

        // Parsing URL with PHP
        $b = false;
        $t_url = $_url;
        if (strncmp($_url, '//', 2) === 0) {
            $b = true;
            $t_url = "http:$_url";
        } elseif (strncmp($_url, '://', 2) === 0) {
            $b = true;
            $t_url = "http$_url";
        } elseif (strpos($_url, '://') === false) {
            $b = true;
            $t_url = "http://$_url";
        }
        $t_url = ltrim($t_url, ':/');
        if (($urlParts = parse_url($t_url)) === false) {
            return;
        }
        $this->m_url = $b ? $_url : $t_url;
        // ---

        // Associate URL parts
        foreach ($urlParts as $key => $val) {
            if ($b && $key == 'scheme') {
                continue;
            }
            $this->m_urlParts[$key] = $val;
        }
        // ---

        // Parsing hostname
        if (is_null($this->m_urlParts['host'])) {
            return;
        }

        $this->m_hostParts['tld'] = substr($this->getLongestSubdomain($this->m_urlParts['host']), 1);
        $this->m_hostParts['subdomain'] = strstr($this->m_urlParts['host'], '.', true);
        $this->m_hostParts['domain'] = substr(
            substr($this->m_urlParts['host'], strlen($this->m_hostParts['subdomain']) + 1),
            0,
            strlen($this->host) - strlen($this->tld) - strlen($this->subdomain) - 2
        );
        // ---
    }

    // -------------------- //

    /**
     * Parse
     *
     * @var     $url    URL to parse
     * @return  UrlParser
     */
    public static function parse($url)
    {
        $class = get_class();
        $p_url = new $class($url);

        return $p_url;
    }

    /**
     * getLongestSubdomain
     *
     * Loop and find longer corresponding subdomain according to PSL
     *
     * @return string   Longer corresponding subdomain with preceding dot (.)
     */
    public function getLongestSubdomain($host)
    {
        $subdomains = array();
        $longestOffset = 0;

        if (!($f_psl = fopen(MOZ_PSL, 'r'))) {
            throw new \Exception("Can't read Mozilla PSL... Make sure it's readable.");
        }

        $i = -1;
        $host_dot_match = preg_match_all('/\./', $host, $host_matches, PREG_OFFSET_CAPTURE);
        while (($psl_buf = rtrim(fgets($f_psl))) && strlen($psl_buf) > 1) {
            $psl_buf = ".$psl_buf";
            if ($psl_buf[1] == '*') {
                $psl_dot_match = preg_match_all('/\./', $psl_buf, $psl_matches, PREG_OFFSET_CAPTURE);

                $b = false;
                for ($j=1,$z=min($psl_dot_match, $host_dot_match) - 1; $j<=$z; $j++) {
                    if (($psl_tld = substr($psl_buf, $psl_matches[0][$psl_dot_match - $j][1]))
                        != ($host_tld = substr($host, $host_matches[0][$host_dot_match - $j][1]))) {
                            $b = true;
                            break;
                    }
                }
                if ($b) { // Didn't match
                    continue;
                }

                $psl_buf = substr($host, $host_matches[0][$host_dot_match - $psl_dot_match][1]);
                $t_tld = $psl_buf;
            } else {
                $t_tld = substr($host, (0 - strlen($psl_buf)));
            }
            if (strcmp($t_tld, $psl_buf) === 0) { // Match: This TLD is OK
                $subdomains[++$i] = $psl_buf;
                if (strlen($psl_buf) > strlen($subdomains[$longestOffset])) {
                    $longestOffset = $i;
                }
            }
        }

        fclose($f_psl);
        return ($i < 0) ? substr($host, $host_matches[0][$host_dot_match - 1][1])
                        : $subdomains[$i];
    }

    /**
     * toArray()
     */
    public function toArray()
    {
        return $this->m_urlParts + $this->m_hostParts;
    }

    /**
     * toString()
     */
    public function toString()
    {
        $url = null;

        if (!is_null($this->m_urlParts['scheme'])) {
            $url .= $this->m_urlParts['scheme'].'://';
        }
        $url .= $this->m_urlParts['user'];
        if (!is_null($this->m_urlParts['pass'])) {
            $url .= ':'.$this->m_urlParts['pass'].'@';
        }
        $url .= $this->m_urlParts['host'];
        if (is_null($this->m_urlParts['path'])) {
            $url .= '/';
        } else {
            $url .= $this->m_urlParts['path'];
        }
        if (!is_null($this->m_urlParts['query'])) {
            $url .= '?'.$this->m_urlParts['query'];
        }
        if (!is_null($this->m_urlParts['fragment'])) {
            $url .= '#'.$this->m_urlParts['fragment'];
        }

        return $url;
    }

    // -------------------- //

    /**
     * isValid
     *
     * @return bool
     */
    public function isValid()
    {
        foreach ($this->m_urlParts as $key => &$val) {
            if (!is_null($val)) {
                return true;
            }
        }

        foreach ($this->m_hostParts as $key => &$val) {
            if (!is_null($val)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Magic method: __set
     */
    public function __set($name, $value)
    {
        foreach ($this->m_urlParts as $key => &$val) {
            if ($key == $name) {
                $this->m_urlParts[$key] = $val;
                return;
            }
        }

        foreach ($this->m_hostParts as $key => &$val) {
            if ($key == $name) {
                $this->m_hostParts[$key] = $val;
                return;
            }
        }

        throw new \Exception("$name isn't a valid property");
    }

    /**
     * Magic method: __get
     */
    public function __get($name)
    {
        foreach ($this->m_urlParts as $key => &$val) {
            if ($key == $name) {
                return $val;
            }
        }

        foreach ($this->m_hostParts as $key => &$val) {
            if ($key == $name) {
                return $val;
            }
        }

        throw new \Exception("$name isn't a valid property");
    }

    /**
     * Magic method: __isset
     */
    public function __isset($name)
    {
        foreach ($this->m_urlParts as $key => &$val) {
            if ($key == $name) {
                return isset($val);
            }
        }

        foreach ($this->m_hostParts as $key => &$val) {
            if ($key == $name) {
                return isset($val);
            }
        }

        return null;
        throw new \Exception("$name isn't a valid property");
    }

    /**
     * Magic method: __unset
     */
    public function __unset($name)
    {
        foreach ($this->m_urlParts as $key => &$val) {
            if ($key == $name) {
                unset($val);
            }
        }

        foreach ($this->m_hostParts as $key => &$val) {
            if ($key == $name) {
                unset($val);
            }
        }

        return;
        throw new \Exception("$name isn't a valid property");
    }

    /**
     * Magic method: __toString
     */
    public function __toString()
    {
        return $this->toString();
    }
}
