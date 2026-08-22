<?php
/* Copyright (C) 2024-2025 EVARISK <technique@evarisk.com>
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
    public string $module = 'priseo';

    /**
     * @var DoliDB Database handler
     */
    public DoliDB $db;

    /**
     * @var string Error code (or message)
     */
    public string $error = '';

    /**
     * @var string[] Array of error strings
     */
    public array $errors = [];

    /**
     * @var array Hook results. Propagated to $hookmanager->resArray for later reuse
     */
    public array $results = [];

    /**
     * @var string|null String displayed by executeHook() immediately after return
     */
    public ?string $resprints;

    /**
     * Constructor
     *
     *  @param DoliDB $db Database handler
     */
    public function __construct(DoliDB $db)
    {
        $this->db = $db;
    }

    /**
     * Overloading the printCommonFooter function : replacing the parent's function with the one below
     *
     * @param  array    $parameters Hook metadata (context, etc...)
     * @return int                  0 < on error, 0 on success, 1 to replace standard code
     * @throws Exception
     */
    public function printCommonFooter(array $parameters): int
    {
        global $object;

        if (strpos($parameters['context'], 'productpricecard') !== false) {
            require_once __DIR__ . '/competitorprice.class.php';

            $competitorPrice = new CompetitorPrice($this->db);

            $competitorPrices = $competitorPrice->fetchAll('DESC', 'amount_ht', 0, 0, ['customsql' => 't.status = ' . CompetitorPrice::STATUS_VALIDATED . ' AND t.fk_product = ' . $object->id]);
            if (!is_array($competitorPrices) || empty($competitorPrices)) {
                return 0;
            }

            $maxPrices = current($competitorPrices);
            $minPrices = end($competitorPrices);

            $competitorPrice->fetch('', '', ' ORDER BY t.rowid DESC');

            $out  = '<tr><td>';
            $out .= img_picto('', $competitorPrice->picto . '_1.2em', 'height="14"') . ucfirst($this->module) . ' (' . dol_print_date($competitorPrice->date_creation, 'day') . ') - ' . price($competitorPrice->getAverage($object->id), 0, '', 1, -1, -1, 'auto') . ' HT</td><td>';

            if (!empty($minPrices->amount_ht) && $object->price > $minPrices->amount_ht) {
                $minPrice = $minPrices->amount_ht;
            } else {
                $minPrice = $object->price;
            }
            $out .= price($minPrice, 0, '', 1, -1, -1, 'auto') . ' HT';

            $out .= ' <= ' . price($object->price, 0, '', 1, -1, -1, 'auto') . ' HT <= ';

            if (!empty($maxPrices->amount_ht) && $object->price < $maxPrices->amount_ht) {
                $maxPrice = $maxPrices->amount_ht;
            } else {
                $maxPrice =  $object->price;
            }
            $out .= price($maxPrice, 0, '', 1, -1, -1, 'auto') . ' HT';

            $out .= '</td></tr>'; ?>

            <script>
                const out = <?php echo json_encode($out); ?>;
                if (out) {
                    $('.field_min_price').after(out);
                }
            </script>
            <?php
        }

        return 0; // or return 1 to replace standard code
    }

    /**
     * Overloading the completeTabsHead function : replacing the parent's function with the one below
     *
     * @param  array $parameters Hook metadata (context, etc...)
     * @return int               0 < on error, 0 on success, 1 to replace standard code
     * @throws Exception
     */
    public function completeTabsHead(array $parameters): int
    {
        global $langs;

        if (strpos($parameters['context'], 'main') !== false) {
            if (!empty($parameters['head'])) {
                foreach ($parameters['head'] as $headKey => $tabHead) {
                    if (!is_array($tabHead) || empty($tabHead)) {
                        continue;
                    }

                    if (isset($tabHead[2]) && $tabHead[2] === 'priseo' && is_string($tabHead[1]) && strpos($tabHead[1], $langs->transnoentities('CompetitorPrice')) !== false && strpos($tabHead[1], 'badge') === false) {
                        require_once __DIR__ . '/competitorprice.class.php';

                        $competitorPrice  = new CompetitorPrice($this->db);
                        $competitorPrices = $competitorPrice->fetchAll('', '', 0, 0, ['customsql' => 't.status >= 0 AND t.fk_product = ' . $parameters['object']->id]);
                        if (!is_array($competitorPrices) || empty($competitorPrices)) {
                            continue;
                        }

                        $parameters['head'][$headKey][1] .= '<span class="badge marginleftonlyshort">' . count($competitorPrices) . '</span>';
                    }
                }
            }
        }

        return 0; // or return 1 to replace standard code
    }
}
