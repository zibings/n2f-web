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
	 * $Id: n2f_paginate.ext.php 192 2012-01-19 20:01:11Z amale@EPSILON $
	 */

	// Pull in globals
	$n2f = n2f_cls::getInstance();

	// Register extension
	$n2f->registerExtension(
		'paginate',
		'n2f_paginate',
		0.2,
		'Andrew Male',
		'http://n2framework.com/'
	);

	/**
	 * Pagination class for N2 Framework Yverdon
	 *
	 */
	class n2f_paginate {
		/**
		 * Current page number
		 *
		 * @var integer
		 */
		public $curr_page;

		/**
		 * Next page number
		 *
		 * @var integer
		 */
		public $next_page;

		/**
		 * Last page number
		 *
		 * @var integer
		 */
		public $last_page;

		/**
		 * Current offset for pagination
		 *
		 * @var integer
		 */
		public $offset;

		/**
		 * Total number of pages
		 *
		 * @var integer
		 */
		public $total_pages;

		/**
		 * Number of entries per page
		 *
		 * @var integer
		 */
		public $per_page;

		/**
		 * Total number of entries
		 *
		 * @var integer
		 */
		public $total_entries;


		/**
		 * Initializes a new n2f_paginate object based on data provided.
		 *
		 * @param integer $currentpage	Current page number
		 * @param integer $total			Total entries in this data set
		 * @param integer $perpage		Number of entries per page
		 * @return n2f_pagination
		 */
		public function __construct($currentpage, $total, $perpage) {
			$this->curr_page = $currentpage;
			$this->per_page = $perpage;
			$this->total_entries = $total;

			$this->calculate();

			return($this);
		}

		/**
		 * Calculates the pagination information based on supplied data.
		 *
		 * @return null
		 */
		private function calculate() {
			if ($this->total_entries < 1) {
				$this->total_pages = 1;
				$this->next_page = 0;
				$this->last_page = 0;
				$this->offset = 0;

				return(null);
			}

			if ($this->curr_page < 1) {
				$this->curr_page = 1;
			}

			if ($this->per_page > $this->total_entries) {
				$this->total_pages = 1;
			} else {
				if (($this->total_entries % $this->per_page) == 0) {
					$this->total_pages = floor($this->total_entries / $this->per_page);
				} else {
					$this->total_pages = floor(($this->total_entries / $this->per_page) + 1);
				}
			}

			if ($this->curr_page > $this->total_pages) {
				$this->curr_page = $this->total_pages;
			}

			$this->offset = (($this->curr_page - 1) * $this->per_page);

			if ($this->curr_page < 2) {
				$this->last_page = 0;
			} else {
				$this->last_page = ($this->curr_page -1);
			}

			if (($this->total_pages - $this->curr_page) < 1) {
				$this->next_page = 0;
			} else {
				$this->next_page = ($this->curr_page + 1);
			}

			return(null);
		}

		/**
		 * Returns an array containing the next few page numbers, useful for displaying page links, etc.
		 *
		 * @param integer $num_pages	Number of page indices to display
		 * @return array
		 */
		public function listPages($num_pages = 5) {
			$ret = array();

			if ($this->curr_page > 0 && $this->total_pages && $num_pages > 0) {
				if ($this->total_pages < $num_pages) {
					$st = 1;
				} else {
					$st = floor($this->curr_page - (($num_pages / 2) - 1));

					if (($this->total_pages - $st) < $num_pages) {
						$st -= (($num_pages - ($this->total_pages - $st)) - 1);
					}

					if ($st < 1) {
						$st = 1;
					}
				}
			}

			for (; $st <= $this->total_pages; $st++) {
				if (count($ret) == $num_pages) {
					break;
				}

				$ret[] = $st;
			}

			return($ret);
		}
	}

?>