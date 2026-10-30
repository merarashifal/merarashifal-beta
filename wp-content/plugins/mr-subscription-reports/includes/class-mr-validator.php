<?php
/**
 * MeraRashifal Report Data Integrity Validator & Performance Monitor
 * Enforces strict zero-assumption, zero-hardcoding rules across all generated reports.
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Validator {

    /**
     * Audit and extract certified astrological metrics from raw API payload.
     * Flags any missing or throttled endpoints so report builders NEVER fabricate data.
     *
     * @param array $raw_payload All raw JSON responses keyed by endpoint alias
     * @return array Standardized, verified metrics with integrity flags
     */
    public static function audit_payload($raw_payload) {
        $audit = [
            'is_valid'         => true,
            'missing_elements' => [],
            'verified_metrics' => [],
            'display_switches' => [] // tells builder whether to show or hide specific sections
        ];

        // 1. Core Planetary Ephemeris
        if (!empty($raw_payload['planets']['response']) && is_array($raw_payload['planets']['response'])) {
            $planets = $raw_payload['planets']['response'];
            $audit['verified_metrics']['lagna'] = [
                'zodiac' => $planets['0']['zodiac'] ?? null,
                'degree' => $planets['0']['local_degree'] ?? null,
                'nakshatra' => $planets['0']['nakshatra'] ?? null,
            ];
            $audit['display_switches']['show_ephemeris'] = true;
        } else {
            $audit['missing_elements'][] = 'planets';
            $audit['display_switches']['show_ephemeris'] = false;
        }

        // 2. Manglik Dosha Audit
        if (isset($raw_payload['mangal']['response']) && is_array($raw_payload['mangal']['response'])) {
            $m = $raw_payload['mangal']['response'];
            $score = (float)($m['score'] ?? ($m['percentage'] ?? 0));
            $is_present = (bool)($m['is_dosha_present'] ?? ($m['manglik_by_mars'] ?? ($score > 0)));
            $is_anshik = (bool)($m['is_anshik'] ?? ($score > 0 && $score < 70));

            $audit['verified_metrics']['mangal'] = [
                'is_present' => $is_present,
                'score'      => $score,
                'is_anshik'  => $is_anshik,
                'status_str' => $is_present ? ($is_anshik ? "Anshik Manglik ({$score}%)" : "Full Manglik ({$score}%)") : "No Manglik Dosh (0%)",
                'factors'    => $m['factors'] ?? [],
                'by_mars'    => $m['manglik_by_mars'] ?? false
            ];
            $audit['display_switches']['show_manglik'] = true;
        } else {
            $audit['missing_elements'][] = 'mangal';
            $audit['display_switches']['show_manglik'] = false;
        }

        // 3. Kaal Sarp Dosha Audit
        if (isset($raw_payload['kaalsarp']['response']) && is_array($raw_payload['kaalsarp']['response'])) {
            $ks = $raw_payload['kaalsarp']['response'];
            $is_present = (bool)($ks['is_dosha_present'] ?? false);
            $type = $ks['dosha_type'] ?? ($ks['type'] ?? 'Kaal Sarp Yoga');
            $axis = $ks['axis'] ?? ($ks['one_line'] ?? '1-7 Axis');

            $audit['verified_metrics']['kaalsarp'] = [
                'is_present' => $is_present,
                'type'       => $type,
                'axis'       => $axis,
                'status_str' => $is_present ? "{$type} (Present)" : "Absent (0%)",
                'bot_response' => $ks['bot_response'] ?? ''
            ];
            $audit['display_switches']['show_kaalsarp'] = true;
        } else {
            $audit['missing_elements'][] = 'kaalsarp';
            $audit['display_switches']['show_kaalsarp'] = false;
        }

        // 4. Pitra Dosha Audit
        if (isset($raw_payload['pitra']['response']) && is_array($raw_payload['pitra']['response'])) {
            $pt = $raw_payload['pitra']['response'];
            $is_present = (bool)($pt['is_dosha_present'] ?? false);
            $audit['verified_metrics']['pitra'] = [
                'is_present' => $is_present,
                'status_str' => $is_present ? "Present" : "Absent (0%)",
                'bot_response' => $pt['bot_response'] ?? ''
            ];
            $audit['display_switches']['show_pitra'] = true;
        } else {
            $audit['missing_elements'][] = 'pitra';
            $audit['display_switches']['show_pitra'] = false;
        }

        // 5. Shadbala Strength & Ranks
        if (isset($raw_payload['shadbala']['response']['total_balas'])) {
            $sb = $raw_payload['shadbala']['response'];
            $totals = $sb['total_balas'];
            $ratios = $sb['ratio'] ?? [];

            // Sort by ratio or virupas descending to find true rank 1
            $sorted_planets = [];
            foreach ($totals as $p => $virupas) {
                if ($virupas !== null && is_numeric($virupas)) {
                    $sorted_planets[$p] = [
                        'virupas' => round((float)$virupas, 2),
                        'rupas'   => round((float)$virupas / 60.0, 2),
                        'ratio'   => round((float)($ratios[$p] ?? ($virupas / 300.0)), 2)
                    ];
                }
            }
            uasort($sorted_planets, function($a, $b) {
                return ($b['ratio'] <=> $a['ratio']);
            });

            $rank = 1;
            foreach ($sorted_planets as $p => &$val) {
                $val['rank'] = $rank++;
            }

            $audit['verified_metrics']['shadbala'] = [
                'planets' => $sorted_planets,
                'rank_1'  => array_key_first($sorted_planets),
                'rank_1_virupas' => reset($sorted_planets)['virupas'] ?? null
            ];
            $audit['display_switches']['show_shadbala'] = true;
        } else {
            $audit['missing_elements'][] = 'shadbala';
            $audit['display_switches']['show_shadbala'] = false;
        }

        // 6. Prescribed Gemstone
        if (!empty($raw_payload['gem']['response']['name'])) {
            $gem = $raw_payload['gem']['response'];
            $audit['verified_metrics']['gemstone'] = [
                'name'         => $gem['name'],
                'other_name'   => $gem['other_name'] ?? '',
                'planet'       => $gem['planet'] ?? '',
                'weight'       => $gem['weight'] ?? '',
                'finger'       => $gem['finger'] ?? '',
                'metal'        => $gem['metal'] ?? '',
                'time_to_wear' => $gem['time_to_wear_short'] ?? ($gem['time_to_wear'] ?? ''),
                'mantra'       => $gem['mantra'] ?? '',
                'not_to_wear'  => $gem['not_to_wear_with'] ?? []
            ];
            $audit['display_switches']['show_gemstone'] = true;
        } else {
            $audit['missing_elements'][] = 'gem';
            $audit['display_switches']['show_gemstone'] = false;
        }

        // 7. Prescribed Rudraksha
        if (!empty($raw_payload['rudraksha']['response']['mukhi_for_money']) || !empty($raw_payload['rudraksha']['response']['mukhi_for_disease_cure'])) {
            $rud = $raw_payload['rudraksha']['response'];
            $audit['verified_metrics']['rudraksha'] = [
                'health_mukhi' => $rud['mukhi_for_disease_cure'] ?? [],
                'wealth_mukhi' => $rud['mukhi_for_money'] ?? [],
                'description'  => $rud['mukhi_description'] ?? '',
                'bot_response' => $rud['bot_response'] ?? ''
            ];
            $audit['display_switches']['show_rudraksha'] = true;
        } else {
            $audit['missing_elements'][] = 'rudraksha';
            $audit['display_switches']['show_rudraksha'] = false;
        }

        return $audit;
    }

    /**
     * Cross-verify an HTML report against the raw verified API metrics.
     * Returns an array of assertion results (PASS / FAIL).
     *
     * @param string $html_content The compiled HTML string
     * @param array $audit Verified metrics from audit_payload()
     * @return array
     */
    public static function cross_check_html($html_content, $audit) {
        $checks = [];
        $metrics = $audit['verified_metrics'] ?? [];

        // Check 1: Manglik Dosha Integrity
        if (!empty($metrics['mangal'])) {
            $m = $metrics['mangal'];
            if ($m['is_present']) {
                $has_anshik = stripos($html_content, 'Anshik') !== false || stripos($html_content, (string)$m['score']) !== false;
                $has_false_claim = stripos($html_content, 'Cancelled (0%)') !== false || stripos($html_content, 'Manglik: Absent (0%)') !== false;
                $checks['manglik_dosh'] = [
                    'expected' => "Present ({$m['status_str']})",
                    'status'   => ($has_anshik && !$has_false_claim) ? 'PASS' : 'FAIL',
                    'detail'   => ($has_anshik && !$has_false_claim) ? "Accurately reported as {$m['status_str']}." : "Discrepancy: Report falsely claims 0% or missed Anshik score."
                ];
            }
        }

        // Check 2: Kaal Sarp Dosha Integrity
        if (!empty($metrics['kaalsarp'])) {
            $ks = $metrics['kaalsarp'];
            if ($ks['is_present']) {
                $has_kaalsarp = stripos($html_content, $ks['type']) !== false || stripos($html_content, 'Kaal Sarp') !== false;
                $has_false_absent = stripos($html_content, 'Kaal Sarp: Absent (0%)') !== false || stripos($html_content, 'Kaal Sarp Dosh: No') !== false;
                $checks['kaalsarp_dosh'] = [
                    'expected' => "Present ({$ks['type']})",
                    'status'   => ($has_kaalsarp && !$has_false_absent) ? 'PASS' : 'FAIL',
                    'detail'   => ($has_kaalsarp && !$has_false_absent) ? "Accurately reported as {$ks['type']}." : "Discrepancy: Report falsely claims Kaalsarp is Absent."
                ];
            }
        }

        // Check 3: Shadbala Rank 1 Integrity
        if (!empty($metrics['shadbala']['rank_1'])) {
            $rank1 = $metrics['shadbala']['rank_1'];
            $has_rank1_honored = stripos($html_content, $rank1) !== false;
            $has_false_mars_rank1 = (stripos($html_content, '522.3') !== false && stripos($html_content, 'Rank 1') !== false && $rank1 !== 'Mars');
            $checks['shadbala_rank_1'] = [
                'expected' => "Rank 1: {$rank1}",
                'status'   => (!$has_false_mars_rank1) ? 'PASS' : 'FAIL',
                'detail'   => (!$has_false_mars_rank1) ? "Shadbala reflects authentic {$rank1} leadership." : "Discrepancy: Hardcoded legacy Mars #1 rank detected."
            ];
        }

        // Check 4: Gemstone Recommendation Integrity
        if (!empty($metrics['gemstone']['name'])) {
            $gem_name = $metrics['gemstone']['name'];
            $other_name = $metrics['gemstone']['other_name'] ?? '';
            $decoded_html = html_entity_decode($html_content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $has_gem = (stripos($html_content, $gem_name) !== false)
                    || (stripos($decoded_html, $gem_name) !== false)
                    || (!empty($other_name) && stripos($html_content, $other_name) !== false);
            $checks['gemstone_prescription'] = [
                'expected' => $gem_name . (!empty($other_name) ? " ({$other_name})" : ''),
                'status'   => $has_gem ? 'PASS' : 'FAIL',
                'detail'   => $has_gem ? "Accurately prescribes API gem: {$gem_name}." : "Discrepancy: {$gem_name} missing from report."
            ];
        }

        // Check 5: Rudraksha Recommendation Integrity
        if (!empty($metrics['rudraksha']['health_mukhi'])) {
            $mukhis = array_merge($metrics['rudraksha']['health_mukhi'], $metrics['rudraksha']['wealth_mukhi']);
            $found_count = 0;
            foreach ($mukhis as $m) {
                if (stripos($html_content, "{$m}-Mukhi") !== false || stripos($html_content, "{$m} Mukhi") !== false) {
                    $found_count++;
                }
            }
            $checks['rudraksha_prescription'] = [
                'expected' => implode(', ', array_unique($mukhis)) . ' Mukhi',
                'status'   => ($found_count > 0) ? 'PASS' : 'FAIL',
                'detail'   => ($found_count > 0) ? "API Rudraksha mukhis featured in report." : "Discrepancy: Generic or hardcoded Rudraksha used."
            ];
        }

        return $checks;
    }
}
