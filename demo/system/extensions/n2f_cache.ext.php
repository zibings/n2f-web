<?php

	/***********************************************\
	 * N2F Yverdon v0                              *
	 * Copyright (c) 2009 Zibings Incorporated     *
	 *                                             *
	 * You should have received a copy of the      *
	 * Microsoft Reciprocal License along with     *
	 * this program.  If not, see:                 *
	 * <http://opensource.org/licenses/ms-rl.html> *
	\***********************************************/

	/*
	 * $Id: n2f_cache.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Global variable(s)
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'cache',
		'n2f_cache',
		0.2,
		'Chris Dougherty',
		'http://n2framework.com/'
	);

	// Pull in our configuration extension
	$n2f->loadExtension('n2f_cache/config');

	// Error constants
	define('CACHE_ERROR_MEMCACHED',			'0001');

	// English error strings
	L('en', 'CACHE_ERROR_MEMCACHED', "The Cache extension was unable to store '_%1%_' in Memcache.");

	// German error strings
	L('de', 'CACHE_ERROR_MEMCACHED', "The Cache extension was unable to store '_%1%_' in Memcache.");

	// Spanish error strings
	L('es', 'CACHE_ERROR_MEMCACHED', "The Cache extension was unable to store '_%1%_' in Memcache.");

	// Swedish error strings
	L('se', 'CACHE_ERROR_MEMCACHED', "The Cache extension was unable to store '_%1%_' in Memcache.");

	/**
	 * Cache class for N2 Framework Yverdon
	 *
	 */
	class n2f_cache {
		/**
		 * Config settings for n2f_cache object.
		 *
		 * @var array
		 */
		private $cfg;

		/**
		 * The Unique ID for the current cache item.
		 *
		 * @var string
		 */
		private $id;

		/**
		 * Use Memcache by default for items stored with the current cache object.
		 *
		 * @var boolean
		 */
		private $memcached;

		/**
		 * Memcache Object.
		 *
		 * @var object
		 */
		private $memcObj;

		/**
		 * The Time-to-Live for the current cache item (in seconds).
		 *
		 * @var integer
		 */
		private $ttl;

		/**
		 * Optional Cache item tag. This will be appended to the current cache item ID.
		 *
		 * @var string
		 */
		private $tag;

		/**
		 * Initializes a new n2f_cache object.
		 *
		 * @param integer $ttl		Default Time-to-Live for cache items created with this object.
		 * @param string $tag		Optional tag to make the cache item more unique (the same tag must be used to retrieve the cache)
		 * @param boolean $memc		Set true to use Memcache by default for all items cached through this object.
		 * @return n2f_cache
		 */
		public function __construct($ttl = null, $tag = "", $memc = null) {
			// Our global config array
			global $cfg;

			// If the cache section of global config is initialized...
			if (isset($cfg['cache']) && is_array($cfg['cache'])) {
				$this->cfg			= $cfg['cache'];
			}

			// Default Settings (if others cannot be found)
			// Cache File Directory (Must be writeable; Will be created automatically if it doesn't exist)
			$this->cfg['dir']			= ((isset($this->cfg['dir'])) ? $this->cfg['dir'] : './n2f_cache/');
			// Cache File Prefix
			$this->cfg['prefix']		= ((isset($this->cfg['prefix'])) ? $this->cfg['prefix'] : '');
			// Cache File Extension
			$this->cfg['ext']			= ((isset($this->cfg['ext'])) ? $this->cfg['ext'] : '.cache');
			// Default amount of time before cache expires  (in seconds, 3600 = 1 hour)
			$this->cfg['ttl']			= ((isset($this->cfg['ttl'])) ? $this->cfg['ttl'] : 3600);
			// Clean up old cache files occasionally
			$this->cfg['gc']			= ((isset($this->cfg['gc'])) ? $this->cfg['gc'] : true);
			// Use Memcache by default
			$this->cfg['memcached']		= ((isset($this->cfg['memcached'])) ? $this->cfg['memcached'] : false);
			// Use persistent Memcache connections
			$this->cfg['mc_persist']		= ((isset($this->cfg['mc_persist'])) ? $this->cfg['mc_persist'] : true);
			// Use compression for MemCache (requires that zlib be installed)
			$this->cfg['mc_compress']	= ((isset($this->cfg['mc_compress'])) ? $this->cfg['mc_compress'] : true);
			// String length required before using compression in MemCache
			$this->cfg['mc_threshold']	= ((isset($this->cfg['mc_threshold'])) ? $this->cfg['mc_threshold'] : 15000);
			// Minimum savings required to actually store the value compressed in MemCache (value between 0 an 1,  0.2 = 20%)
			$this->cfg['mc_savings']		= ((isset($this->cfg['mc_savings'])) ? $this->cfg['mc_savings'] : 0.2);
			// MemCache Servers - format:  'server:port' => 'weight'
			$this->cfg['mc_servers']		= ((isset($this->cfg['mc_servers'])) ? $this->cfg['mc_servers'] : array('localhost:11211' => '64'));

			// Set this toggle up
			$this->memcached = $this->cfg['memcached'];

			// Override the configured setting with the supplied setting
			if (($memc != null) && ($memc != $this->cfg['memcached'])) {
				$this->memcached = $memc;
			}

			// Override the configured setting with the supplied setting
			if (($ttl != null) && (is_numeric($ttl))) {
				$this->ttl = $ttl;
				$this->cfg['ttl'] = $ttl;
			}

			// Set the tag
			$this->tag = $tag;

			// If we're meant to cache in memory, let's get ready
			if (function_exists("memcache_connect")) {
				$this->memcObj = new Memcache;

				foreach ($this->cfg['mc_servers'] as $server => $weight) {
					list($host, $port) = explode(':', $server);
					$this->memcObj->addServer($host, $port, $this->cfg['mc_persist'], $weight);
				}
			} else { // Otherwise, we just simply can't cache in memory
				$this->memcached = false;
				$this->cfg['memcached'] = false;
			}

			// Return for make-believe chaining
			return($this);
		}


		/**
		 * Write function wrapper
		 *
		 * @param string $id		Unique ID for this item
		 * @param integer $ttl		Amount of time before this cache item expires (in seconds)
		 * @param mixed $data		Data to be cached
		 * @param boolean $memcache	Whether or not to use Memcache
		 */
		protected function writer($id, $ttl, $data, $memcache = null) {
			// If we're using memcached, put the data to that bugger and get out
			if ($memcache === true) {
				$this->mwrite($id, $ttl, $data);

				return;
			}

			// Otherwise, send to normal write
			$this->write($id, $ttl, $data);

			// And get out
			return;
		}

		/**
		 * Stores cached data
		 *
		 * @param string $id		Unique ID for this data
		 * @param integer $ttl		Amount of time before this cache expires (in seconds)
		 * @param mixed $data		Data to be cached
		 */
		protected function write($id, $ttl, $data) {
			// Generate the filename for storage
			$filename = $this->makeFilename($id);

			// Try opening the file for binary-write
			if ($filep = fopen($filename, 'wb')) {
				// If we can get the lock, write our data
				if (flock($filep, LOCK_EX)) {
					fwrite($filep, $data);
				}

				// Close our data and touch the time
				fclose($filep);
				touch($filename, time() + $ttl);
			}
		}

		/**
		 * Stores data to Memcache
		 *
		 * @param string $id		Unique ID for this data
		 * @param integer $ttl		Amount of time before this cache item expires (in seconds)
		 * @param mixed $data		Data to be cached
		 */
		protected function mwrite($id, $ttl, $data) {
			// Grab our global n2f_cls instance and make our write-key
			$n2f = n2f_cls::getInstance();
			$key = $this->makeKey($id);

			// Initialize our local variables
			$mem_compress = false;
			$result = null;

			// If we're configured to do compression
			if ($this->cfg['mc_compress'] === true) {
				// If we have the compress stuff available, compress and say we did
				if (function_exists("memcache_set_compress_threshold")) {
					$this->memcObj->setCompressThreshold($this->cfg['mc_threshold'], $this->cfg['mc_savings']);
					$mem_compress = true;
				}
			}

			// If the item isn't cached
			if (!$this->isCached($id, "", true)) {
				// If we fail replacing the item in memory, try setting it
				if (!$result = $this->memcObj->replace($key, $data, (($mem_compress === false) ? 0 : MEMCACHE_COMPRESSED), $ttl)) {
					$result = $this->memcObj->set($key, $data, (($mem_compress === false) ? 0 : MEMCACHE_COMPRESSED), $ttl);
				}
			}

			// If $result == false, we failed.. Need to throw an error or something...
			if ($result === false) {
				$n2f->debug->throwError(CACHE_ERROR_MEMCACHED, S('CACHE_ERROR_MEMCACHED', array($id)), 'system/extensions/cache.ext.php');
			}
		}

		/**
		 * Builds the /path/filename  -  Creates the cache directory if it does not exist
		 *
		 * @param string $id		Unique ID for this file
		 * @param string $tag		Optional tag to make this cache file more unique (the same tag must be used to retrieve the cache)
		 * @return string			Path and Filename of cache file
		 */
		protected function makeFilename($id, $tag = "") {
			// Create the cache directory if it doesn't exist
			if (!is_dir($this->cfg['dir'])) {
				mkdir($this->cfg['dir'], 0777, true);
			}

			// Bit of initialization
			clearstatcache();
			$ftag = "";

			// If we have a tag set, configure the object
			if ($tag != "") {
				$this->tag = $tag;
			}

			// If the object has a tag configured, add it to the tag string
			if ($this->tag != "") {
				$ftag = "-{$this->tag}";
			}

			// Hash the given identifier
			$hash = sha1($id);

			// Return the generated filename
			return("{$this->cfg['dir']}{$this->cfg['prefix']}{$hash}{$ftag}{$this->cfg['ext']}");
		}

		/**
		 * Formats a Key string for storing an item in Memcache
		 *
		 * @param string $id		Unique ID for this item
		 * @param string $tag		Optional tag to make this cache item key more unique (the same tag must be used to retrieve this cache item)
		 * @return string			Key for this cache item
		 */
		protected function makeKey($id, $tag = "") {
			// Initialize variable
			$ftag = "";

			// If we have a tag set, configure the object
			if ($tag != "") {
				$this->tag = $tag;
			}

			// If the object has a tag configured, add it to the tag string
			if ($this->tag != "") {
				$ftag = "-{$this->tag}";
			}

			// Hash the given identifier
			$hash = sha1($id);

			// Return the generated key
			return("{$this->cfg['prefix']}{$hash}{$ftag}");
		}

		/**
		 * Read function wrapper
		 *
		 * @param string $id		Unique ID for this item
		 * @param boolean $memcache	Whether or not to use Memcache
		 * @return string			Contents of the cached item
		 */
		protected function reader($id, $memcache = null) {
			// If we're using memcached method
			if ($memcache === true) {
				return($this->mread($id));
			}

			// Otherwise, do normal fs read
			return($this->read($id));
		}
		/**
		 * Reads data from cache
		 *
		 * @param string $id		Unique ID for this file
		 * @return string			Contents of cache file
		 */
		protected function read($id) {
			// Generate the filename
			$filename = $this->makeFilename($id);

			// Return the contents
			return(file_get_contents($filename));
		}

		/**
		 * Reads data from Memcache
		 *
		 * @param string $id		Unique ID for this item
		 * @return string			Contents of cache item
		 */
		protected function mread($id) {
			// Generate the key
			$key = $this->makeKey($id);

			// Return the contents
			return($this->memcObj->get($key));
		}

		/**
		 * Cleans the cache directory periodically.
		 *
		 */
		public function cleanCache() {
			// If we're told to do garbage collection
			if ($this->cfg['gc'] === true) {
				// If the cache directory doesn't exist, leave
				if (!is_dir($this->cfg['dir'])) {
					return;
				}

				// If this is a 5 second interval
				if ((time() % 5) == 0) {
					// If we could open the directory
					if ($dh = @opendir($this->cfg['dir'])) {
						// Clear the stat stuff
						clearstatcache();

						// While the read contents are available
						while (($file = readdir($dh)) !== false) {
							// Toggle for whether or not to delete the file
							$del_file = false;

							// If it's not a directory
							if (!is_dir($file)) {
								// If it has the right file extension, try delete
								if (($this->cfg['ext'] != "") && (strpos($file, $this->cfg['ext']) !== false)) {
									$del_file = true;
								} else if ($this->cfg['ext'] == "") { // Or if we don't -use- an extension, try delete
									$del_file = true;
								} else { // Otherwise, keep it false
									$del_file = false;
								}

								// If we're to try deletion
								if ($del_file) {
									// Make sure the file exists and has expired before deleting
									if (file_exists($this->cfg['dir'].$file) && (@filemtime($this->cfg['dir'].$file) < time())) {
										@unlink($this->cfg['dir'].$file);
									}
								}
							}
						}

						// Close directory for cleanliness
						@closedir($dh);
					}
				}
			}
		}

		/**
		 * Checks if $id is cached
		 *
		 * @param string $id		Unique ID for this cache
		 * @param string $tag		Optional tag that was used when this cache was created
		 * @param boolean $memcache	Use Memcache
		 * @return boolean
		 */
		public function isCached($id, $tag = "", $memcache = null) {
			// If we're not doing it right, grab the configured value
			if (($memcache !== true) && ($memcache !== false)) {
				$memcache = $this->memcached;
			}

			// If we're using memcached, grab the item from there
			if ($memcache === true) {
				$key = $this->makeKey($id, $tag);

				return($this->memcObj->get($key));
			}

			// Generate the filename
			$filename = $this->makeFilename($id, $tag);

			// If the file exists and hasn't expired, we're golden
			if (file_exists($filename) && (filemtime($filename) > time())) {
				return(true);
			}

			// Delete cache file if it's older than the Time-to-Live
			@unlink($filename);
			$this->cleanCache();

			// We're not valid
			return(false);
		}

		/**
		 * Checks if $id is cached, if true it returns the cache.
		 * If false, it returns FALSE.
		 *
		 * @param string $id		Unique ID for this item
		 * @param string $tag		Optional tag to make this cache item ID more unique (the sam tag must be used to retrieve the cache)
		 * @param boolean $memcache	If true, force fetching this item from Memcache; if false, use the default storage method (which may well be Memcache)
		 * @return mixed			Will return the cached data if available. Otherwise it returns FALSE.
		 */
		public function getCache($id, $tag = "", $memcache = null) {
			// If the tag is being set, configure that as the new one
			if ($tag != "") {
				$this->tag = $tag;
			}

			// If we have an invalid memcache value, set from configuration
			if (($memcache !== true) && ($memcache !== false)) {
				$memcache = $this->memcached;
			}

			// If the item is cached, grab it
			if ($this->isCached($id, $tag, $memcache)) {
				$contents = $this->reader($id, $memcache);

				return($contents);
			}

			// Otherwise, we have failed
			return(false);
		}

		/**
		 * Checks if $id is already cached, if true it returns the cache.
		 * If false, it starts buffering everything until $this->endCaching() is called.
		 * Use this for caching compiled PHP, HTML, Text, etc..
		 *
		 * @param string $id		Unique ID for this item
		 * @param integer $ttl		Amount of time before this cache expires (in seconds)
		 * @param string $tag		Optional tag to make this cache item ID more unique (the same tag must be used to retrieve the cache)
		 * @param boolean $memcache	If true, store this item in Memcache; if false, store in a file; otherwise use the default storage method (which may well be Memcache)
		 * @param boolean $return	If true and the item is cached, return the contents of the cache; otherwise echo the contents of the cache
		 */
		public function startCaching($id, $ttl = null, $tag = "", $memcache = null, $return = false) {
			// If the tag is there, reset the configured tag (how many different ways am I going to say that?)
			if ($tag != "") {
				$this->tag = $tag;
			}

			// If memcahe value isn't proper, use configured value
			if (($memcache !== true) && ($memcache !== false)) {
				$memcache = $this->memcached;
			}

			// If we can get the contents
			if ($contents = $this->getCache($id, $tag, $memcache)) {
				// And we're meant to return, go ahead and return the contents
				if ($return) {
					return($contents);
				} else { // Otherwise, echo the contents and give a thumbs up
					echo($contents);

					return(true);
				}
			} else { // Otherwise, we're about to cache something
				// Start output buffering
				ob_start();

				// Toggle time to live if we don't have it provided
				if ($ttl == null) {
					$ttl = $this->cfg['ttl'];
				}

				// And initialize our variables
				$this->id = $id;
				$this->ttl = $ttl;

				// Then return thumbs down
				return(false);
			}
		}

		/**
		 * Ends caching and writes the buffer data to disk.
		 *
		 * @param boolean $return	Determines if you want the buffer to be output.
		 * @param boolean $memcache	If true, store this item in Memcache; if false, store in a file; otherwise use the default storage method (which may well be Memcache)
		 */
		public function endCaching($return = true, $memcache = null) {
			// Grab the contents and stop output buffering
			$data = ob_get_contents();
			ob_end_clean();

			// If we're missing a valid memcache setting, load from config
			if (($memcache !== true) && ($memcache !== false)) {
				$memcache = $this->memcached;
			}

			// Push the data through to our writer
			$this->writer($this->id, $this->ttl, $data, $memcache);

			// If we're returning, echo the data
			if ($return) {
				echo($data);
			}
		}

		/**
		 * Gets serialized data from the cache.
		 *
		 * @param string $id		Unique ID for this item.
		 * @param string $tag		The tag used when this data was cached (Optional)
		 * @param boolean $memcache	Whether or not to use Memcache
		 * @return mixed			Resulting data or null.
		 */
		public function getObject($id, $tag = "", $memcache = null) {
			// If we're overriding the tag, do so
			if ($tag != "") {
				$this->tag = $tag;
			}

			// If not valid, set config value
			if (($memcache !== true) && ($memcache !== false)) {
				$memcache = $this->memcached;
			}

			// If the item is cached
			if ($this->isCached($id, $tag, $memcache)) {
				// Grab the cached data
				$retVal = $this->reader($id, $memcache);

				// If it was a memcache pull, decode it from matrix language
				if ($memcache !== true) {
					$retVal = unserialize($retVal);
				}

				// Return the data
				return($retVal);
			}

			// Return blank, as we've got nothing man
			return(null);
		}

		/**
		 * Caches data in serialized form.
		 * Use this for caching Arrays/Objects, such as MySQL Query result arrays.
		 *
		 * @param string $id		Unique ID for this data
		 * @param mixed $data		Data to be cached
		 * @param integer $ttl		Amount of time before this cache expires (in seconds)
		 * @param string $tag		Optional tag to make this cache item ID more unique (the same tag must be used to retrieve the cache)
		 * @param boolean $memcache	If true, store this item in Memcache; if false, store in a file; otherwise use the default storage method (which may well be Memcache)
		 */
		public function setObject($id, $data, $ttl = null, $tag = "", $memcache = null) {
			// If not valid, set the config value
			if (($memcache !== true) && ($memcache !== false)) {
				$memcache = $this->memcached;
			}

			// This is for backwards compatibility, cause we're bosses like that
			if (is_int($data) && !is_int($ttl)) {
				$tmp = $data;
				$data = $ttl;
				$ttl = $tmp;
			}

			// If we don't have ttl, let's set it up from config
			if ($ttl == null) {
				$ttl = $this->cfg['ttl'];
			}

			// If we override the tag, override the tag
			if ($tag != "") {
				$this->tag = $tag;
			}

			// Send to the writer
			$this->writer($id, $ttl, (($memcache === true) ? $data : serialize($data)), $memcache);
		}

		/**
		 * Delete function wrapper
		 *
		 * @param string $id		Unique ID for this item
		 * @param string $tag		The tag used when this data was cached (Optional)
		 * @param boolean $memcache	Whether or not to use Memcache
		 * @param integer $timeout	How log to wait before deleting the item (in seconds;  Only applies if $memcache = true)
		 * @return boolean
		 */
		public function delete($id, $tag = "", $memcache = null, $timeout = 0) {
			// Override the tag if we're told
			if ($tag != "") {
				$this->tag = $tag;
			}

			// If we're using memcached, return result of memcached delete
			if ($memcache === true) {
				return($this->mdelete($id, $timeout));
			}

			// Return result of filesystem delete
			return($this->fdelete($id));
		}

		/**
		 * Deletes data from file cache (Use the delete wrapper function)
		 *
		 * @param string $id		Unique ID for this item
		 * @param string $tag		The tag used when this data was cached (Optional)
		 * @return boolean
		 */
		protected function fdelete($id) {
			// Generate filename
			$filename = $this->makeFilename($id);

			// Scope freshens breath and destroys values!
			$retVal = false;

			// If the file is there
			if (file_exists($filename)) {
				// Delete cache file
				$retVal = @unlink($filename);
				$this->cleanCache();
			}

			// Return our value, scope accounted for
			return($retVal);
		}

		/**
		 * Deletes data from Memcache (Use the delete wrapper function)
		 *
		 * @param string $id		Unique ID for this item
		 * @param integer $timeout	How log to wait before deleting the item (in seconds)
		 * @return boolean
		 */
		protected function mdelete($id, $timeout = 0) {
			// Generate the key
			$key = $this->makeKey($id);

			// Return result of delete
			return($this->memcObj->delete($key, $timeout));
		}
	}

?>