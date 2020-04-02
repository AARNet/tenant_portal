<?php

namespace OCA\Tenant_Portal\Managers;

use OCA\Tenant_Portal\Util;

class StatManager {

    private $statService;

	public function __construct() {
	    $this->statService = Util::getStatService();
	}

	/**
	* Create a new stat record
	* @param integer $tenant_id
	* @param string $key
	* @param string $value
	*/
	public function create($tenant_id, $key, $value, $timestamp=null) {
		return $this->statService->create($tenant_id, $key, $value, $timestamp=null);
	}

	/**
	 * Find config records for a tenant by key
	 * @param integer $tenant_id
	 * @param string $configkey
	 * @return Stat[]
	 */
	public function findAllByKey($tenant_id, $configkey) {
		return $this->statService->findAllByKey($tenant_id, $configkey);
	}

	/**
	 * Build a chart label/value array
	 * @param Stat $stat
	 * @param integer $pad
	 * @return $result
	 */
        public function buildChartArray($stats, $pad=null, $limit=12) {
                $processed_stats = [];

                // Build Array
                foreach ($stats as $stat) {
                        $value = $stat->getStatValue();
                        $yearmonth = \date('Y-m', strtotime($stat->getTimestamp()));
                        $day = \date('d', strtotime($stat->getTimestamp()));

                        // Make sure the year-month key exists
                        if (!\array_key_exists($yearmonth, $processed_stats)) {
                                $processed_stats[$yearmonth] = Array();
                        }

                        // Make sure there is only one value per day
                        if (!\in_array($day, array_keys($processed_stats[$yearmonth]))) {
                                $processed_stats[$yearmonth][$day] = (int) $value;
                        }
                }

                // Sort the days of each month and set the month to the last value
                foreach (\array_keys($processed_stats) as $month) {
                        \ksort($processed_stats[$month]);
                        if (!empty($processed_stats[$month])) {
                                $processed_stats[$month] = \array_pop($processed_stats[$month]);
                        } else {
                                $processed_stats[$month] = null;
                        }
                }

                // Pad out array to contain $pad values
                if ($pad !== null) {
                        $keys = \array_keys($processed_stats);
                        if (!empty($keys)) {
                                $lastTimestamp = \strtotime(end($keys));
                        } else {
                                $lastTimestamp = \strtotime("now");
                        }
                        for ($i = $lastTimestamp; \count($processed_stats) < $pad; $i = \strtotime("-1 months", $i)) {
                                $timestamp = date('Y-m', $i);
                                if (!\in_array($timestamp, array_keys($processed_stats))) {
                                        $processed_stats[$timestamp] = null;
                                }
                        }
                }
                ksort($processed_stats);
                if ($limit && $limit > 0) {
                        $processed_stats = \array_slice($processed_stats, -$limit, $limit);
                }
                return [ "labels" => \array_keys($processed_stats), "values" =>  \array_values($processed_stats)];
        }

	/**
	 * Build a chart label/value array
	 * @param Stat $stat
	 * @param integer $pad
	 * @return $result
	 */
	public function buildCSVArray($stats, $pad=null) {
		$result = [];

		// Build Array
		foreach ($stats as $stat) {
			$value = $stat->getStatValue();
			$timestamp = date('Y-m-d', strtotime($stat->getTimestamp()));

			// Make sure there is only one value per day
			if (!in_array($timestamp, array_keys($result))) {
				$result[$timestamp] = (int) $value;
			}
		}	
		ksort($result);
		$data = Array();
		foreach ($result as $key => $value) {
			array_push($data, Array($key, $value));
		}
		return $data;
	}

	/**
	 * Function to convert an Array[] to a CSV string
	 * @param Array[] $data
	 * @return string
	 */
	public function generateCSV($data) {
		$csvdata = NULL;
		foreach ($data as $line) {
			$fp = fopen('php://temp', 'r+b');
			fputcsv($fp, $line);
			rewind($fp);
			$csvdata .= fgets($fp);
			fclose($fp);
		}
		return $csvdata;
	}
}
