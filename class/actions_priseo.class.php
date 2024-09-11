<?php
/* Copyright (C) 2024 EVARISK <technique@evarisk.com>
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <https://www.gnu.org/licenses/>.
 */

/**
 * \file    class/actions_priseo.class.php
 * \ingroup priseo
 * \brief   Priseo hook overload
 */

/**
 * Class ActionsPriseo
 */
class ActionsPriseo
{
    /**
     * @var string Module name
     */
    public $module = 'priseo';

    /**
     * @var DoliDB Database handler
     */
    public $db;

    /**
     * @var string Error string
     */
    public $error;

    /**
     * @var string[] Array of error strings
     */
    public $errors = [];

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public $results = [];

    /**
     * @var string|null String displayed by executeHook() immediately after return.
     */
    public ?string $resprints;


    /**
     * Constructor
     *
     *  @param DoliDB $db Database handler
     */
    public function __construct($db)
    {
        $this->db = $db;
    }

    /**
     * Overloading the printCommonFooter function : replacing the parent's function with the one below
     *
     * @param  array      $parameters Hook metadatas (context, etc...)
     * @return int                    0 < on error, 0 on success, 1 to replace standard code
     * @throws Exception
     */
    public function printCommonFooter(array $parameters): int
    {
        global $object;

        if (strpos($parameters['context'], 'productpricecard') !== false) {
            require_once __DIR__ . '/competitorprice.class.php';

            $competitorPrice = new CompetitorPrice($this->db);

            $competitorPrices = $competitorPrice->fetchAll('DESC', 'amount_ht', 0, 0, ['customsql' => 't.status = ' . $competitorPrice::STATUS_VALIDATED . ' AND t.fk_product = ' . $object->id]);
            if (is_array($competitorPrices) && !empty($competitorPrices)) {
                $maxPrices = array_shift($competitorPrices);
                $minPrices = end($competitorPrices);

                $competitorPrice->fetch('', '', ' ORDER BY t.rowid DESC');

                $out  = '<tr><td>';
                $out .= img_picto('', $competitorPrice->picto . '_1.2em', 'class="pictoPriseo"') . ucfirst($this->module) . ' (' . dol_print_date($competitorPrice->date_creation, 'day') . ') - ' . price($competitorPrice->getAverage($object->id), 0, '', 1, -1, -1, 'auto') . ' HT</td><td>';
                $out .= $object->price < $minPrices->amount_ht ? price($object->price, 0, '', 1, -1, -1, 'auto') . ' HT': price($minPrices->amount_ht, 0, '', 1, -1, -1, 'auto') . ' HT';
                $out .= ' <= ' . price($object->price, 0, '', 1, -1, -1, 'auto') . ' HT <= ';
                $out .= $maxPrices->amount_ht < $object->price ? price($object->price, 0, '', 1, -1, -1, 'auto') . ' HT': price($maxPrices->amount_ht, 0, '', 1, -1, -1, 'auto') . ' HT';
                $out .= '</td></tr>';
                ?>
                <script>
                    $('.field_min_price').after(<?php echo json_encode($out); ?>);
                </script>
                <?php
            }
        }
        return 0; // or return 1 to replace standard code
    }

	/**
	 * Overloading the completeTabsHead function : replacing the parent's function with the one below
	 *
	 * @param  array $parameters Hook metadatas (context, etc...)
	 * @return int               0 < on error, 0 on success, 1 to replace standard code
	 */
	public function completeTabsHead(array $parameters): int
	{
		global $langs;

		if (strpos($parameters['context'], 'main') !== false) {
			if (!empty($parameters['head'])) {
				foreach ($parameters['head'] as $headKey => $tabsHead) {
					if (is_array($tabsHead) && !empty($tabsHead)) {
						if (isset($tabsHead[2]) && $tabsHead[2] === 'priseo' && strpos($tabsHead[1], $langs->transnoentities('CompetitorPrice')) && !strpos($parameters['head'][$headKey][1], 'badge')) {
							require_once __DIR__ . '/competitorprice.class.php';

							$competitorPrice  = new CompetitorPrice($this->db);
							$competitorPrices = $competitorPrice->fetchAll('', '', 0, 0, ['customsql' => 't.status >= 0 AND t.fk_product = ' . $parameters['object']->id]);
							if (is_array($competitorPrices) && !empty($competitorPrices)) {
                                $parameters['head'][$headKey][1] .= '<span class="badge marginleftonlyshort">' . count($competitorPrices) . '</span>';
                            }
						}
					}
				}
			}

			$this->results = $parameters;
		}

		return 0; // or return 1 to replace standard code
	}
}
