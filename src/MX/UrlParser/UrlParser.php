<?php
namespace   MX\UrlParser;

/**
 * @brief       MXUrlParser
 *
 * @details     URL/URI parser
 *
 * @version     0.1.0
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
    const VERSION = '0.1.0';

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
            throw new \Exception('Missing Mozilla PSL...');
        }
        // ---

        // Parsing URL with PHP
        $this->m_url = ltrim($_url, ':/');
        if (($urlParts = parse_url($this->m_url)) === false) {
            return;
        }
        // ---

        // Associate URL parts
        foreach ($urlParts as $key => &$val) {
            $this->m_urlParts[$key] = $val;
        }
        // ---

        // Parsing hostname
        if (is_null($this->m_urlParts['host'])) {
            return;
        }

        $subdomain = $this->getLongestSubdomain($this->m_urlParts['host']);

        while (($buffer = rtrim(fgets($f_psl)))) {
            if ($buffer[0] != '*') {
                $t_tld = substr($this->m_urlParts['host'], -(strlen($buffer) + 1));
                if (strcmp($t_tld, ".$buffer") === 0) { // Match: This TLD is OK
                    
                    $this->m_hostParts['tld'] = $buffer;
                    $this->m_hostParts['subdomain'] = strstr($this->m_urlParts['host'], '.', true);
                    $this->m_hostParts['domain'] = substr(
                        substr($this->m_urlParts['host'], strlen($this->m_hostParts['subdomain']) + 1),
                        0,
                        strlen($this->m_urlParts['host']) - strlen($this->m_hostParts['tld']) - strlen($this->m_hostParts['subdomain']) - 2
                    );
                    break;
                } else { // Wildcard !
                }
            }
        }
        // ---
    }

    // -------------------- //

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
            throw new \Exception('Can\'t read Mozilla PSL...');
        }

        $i = -1;
        while (($psl_buf = '.'.rtrim(fgets($f_psl))) && strlen($psl_buf) > 1) {
            if ($psl_buf[1] == '*') {
                continue; // For now
                $psl_dot_count = substr_count($psl_buf, '.');
                $host_dot_match = preg_match_all('/\./', $host, $host_matches, PREG_OFFSET_CAPTURE);
                $psl_buf = substr($host_matches, $host_matches[$host_dot_match - 2][1]);
                $psl_buf_len = strlen($psl_buf);
            } else {
                $psl_buf_len = strlen($psl_buf);
                $t_tld = substr($host, 0 - $psl_buf_len);
                if (strcmp($t_tld, $psl_buf) === 0) { // Match: This TLD is OK
                    $subdomains[++$i] = $psl_buf;
                    if (strlen($psl_buf) > strlen($subdomains[$longestOffset])) {
                        $longestOffset = $i;
                    }
                }
            }
        }

        fclose($f_psl);
        return $subdomains[$i];
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
    }
}
