<?php
/**
 * MeraRashifal Baby Name Engine: Deterministic Vedic Astrology & Numerology Core
 * Class: MR_Baby_Astro_Engine
 *
 * Implements 100% deterministic mathematical calculations for Vedic astrology
 * (Rashi, Nakshatra, Pada, Graha Swami, Deity, Element) and Numerology
 * (Chaldean, Pythagorean, Soul Urge, Personality) to ensure zero hallucination.
 *
 * @package MeraRashifal
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Baby_Astro_Engine
{
    private static $instance = null;

    /**
     * Chaldean Alphabet Mapping
     * (Vedic Chaldean system: 9 is sacred and excluded from letter assignment)
     */
    private $chaldean_map = [
        'A' => 1, 'I' => 1, 'J' => 1, 'Q' => 1, 'Y' => 1,
        'B' => 2, 'K' => 2, 'R' => 2,
        'C' => 3, 'G' => 3, 'L' => 3, 'S' => 3,
        'D' => 4, 'M' => 4, 'T' => 4,
        'E' => 5, 'H' => 5, 'N' => 5, 'X' => 5,
        'U' => 6, 'V' => 6, 'W' => 6,
        'O' => 7, 'Z' => 7,
        'F' => 8, 'P' => 8
    ];

    /**
     * Western / Pythagorean Alphabet Mapping (1 to 9)
     */
    private $pythagorean_map = [
        'A' => 1, 'B' => 2, 'C' => 3, 'D' => 4, 'E' => 5, 'F' => 6, 'G' => 7, 'H' => 8, 'I' => 9,
        'J' => 1, 'K' => 2, 'L' => 3, 'M' => 4, 'N' => 5, 'O' => 6, 'P' => 7, 'Q' => 8, 'R' => 9,
        'S' => 1, 'T' => 2, 'U' => 3, 'V' => 4, 'W' => 5, 'X' => 6, 'Y' => 7, 'Z' => 8
    ];

    /**
     * 108 Vedic Nakshatra Pada Syllable Matrix
     * Maps starting phonetic sounds to specific Nakshatra, Pada (1-4), and Rashi
     */
    private $pada_matrix = [
        // 1. Ashwini (Aries / Mesh)
        'CHU' => ['nakshatra' => 'Ashwini', 'pada' => 1, 'rashi' => 'Mesh'],
        'CHE' => ['nakshatra' => 'Ashwini', 'pada' => 2, 'rashi' => 'Mesh'],
        'CHO' => ['nakshatra' => 'Ashwini', 'pada' => 3, 'rashi' => 'Mesh'],
        'LA'  => ['nakshatra' => 'Ashwini', 'pada' => 4, 'rashi' => 'Mesh'],

        // 2. Bharani (Aries / Mesh)
        'LEE' => ['nakshatra' => 'Bharani', 'pada' => 1, 'rashi' => 'Mesh'],
        'LI'  => ['nakshatra' => 'Bharani', 'pada' => 1, 'rashi' => 'Mesh'],
        'LU'  => ['nakshatra' => 'Bharani', 'pada' => 2, 'rashi' => 'Mesh'],
        'LOO' => ['nakshatra' => 'Bharani', 'pada' => 2, 'rashi' => 'Mesh'],
        'LE'  => ['nakshatra' => 'Bharani', 'pada' => 3, 'rashi' => 'Mesh'],
        'LO'  => ['nakshatra' => 'Bharani', 'pada' => 4, 'rashi' => 'Mesh'],

        // 3. Krittika (Pada 1 Aries, Pada 2-4 Taurus / Vrishabh)
        'AA'  => ['nakshatra' => 'Krittika', 'pada' => 1, 'rashi' => 'Mesh'],
        'A'   => ['nakshatra' => 'Krittika', 'pada' => 1, 'rashi' => 'Mesh'],
        'EE'  => ['nakshatra' => 'Krittika', 'pada' => 2, 'rashi' => 'Vrishabh'],
        'E'   => ['nakshatra' => 'Krittika', 'pada' => 2, 'rashi' => 'Vrishabh'],
        'OO'  => ['nakshatra' => 'Krittika', 'pada' => 3, 'rashi' => 'Vrishabh'],
        'U'   => ['nakshatra' => 'Krittika', 'pada' => 3, 'rashi' => 'Vrishabh'],
        'AE'  => ['nakshatra' => 'Krittika', 'pada' => 4, 'rashi' => 'Vrishabh'],
        'AI'  => ['nakshatra' => 'Krittika', 'pada' => 4, 'rashi' => 'Vrishabh'],

        // 4. Rohini (Taurus / Vrishabh)
        'O'   => ['nakshatra' => 'Rohini', 'pada' => 1, 'rashi' => 'Vrishabh'],
        'VA'  => ['nakshatra' => 'Rohini', 'pada' => 2, 'rashi' => 'Vrishabh'],
        'BA'  => ['nakshatra' => 'Rohini', 'pada' => 2, 'rashi' => 'Vrishabh'],
        'VI'  => ['nakshatra' => 'Rohini', 'pada' => 3, 'rashi' => 'Vrishabh'],
        'VEE' => ['nakshatra' => 'Rohini', 'pada' => 3, 'rashi' => 'Vrishabh'],
        'BI'  => ['nakshatra' => 'Rohini', 'pada' => 3, 'rashi' => 'Vrishabh'],
        'VU'  => ['nakshatra' => 'Rohini', 'pada' => 4, 'rashi' => 'Vrishabh'],
        'VOO' => ['nakshatra' => 'Rohini', 'pada' => 4, 'rashi' => 'Vrishabh'],

        // 5. Mrigashira (Pada 1-2 Taurus, Pada 3-4 Gemini / Mithun)
        'VE'  => ['nakshatra' => 'Mrigashira', 'pada' => 1, 'rashi' => 'Vrishabh'],
        'VO'  => ['nakshatra' => 'Mrigashira', 'pada' => 2, 'rashi' => 'Vrishabh'],
        'KA'  => ['nakshatra' => 'Mrigashira', 'pada' => 3, 'rashi' => 'Mithun'],
        'KI'  => ['nakshatra' => 'Mrigashira', 'pada' => 4, 'rashi' => 'Mithun'],
        'KEE' => ['nakshatra' => 'Mrigashira', 'pada' => 4, 'rashi' => 'Mithun'],

        // 6. Ardra (Gemini / Mithun)
        'KU'  => ['nakshatra' => 'Ardra', 'pada' => 1, 'rashi' => 'Mithun'],
        'KOO' => ['nakshatra' => 'Ardra', 'pada' => 1, 'rashi' => 'Mithun'],
        'GHA' => ['nakshatra' => 'Ardra', 'pada' => 2, 'rashi' => 'Mithun'],
        'NG'  => ['nakshatra' => 'Ardra', 'pada' => 3, 'rashi' => 'Mithun'],
        'CHHA'=> ['nakshatra' => 'Ardra', 'pada' => 4, 'rashi' => 'Mithun'],

        // 7. Punarvasu (Pada 1-3 Gemini, Pada 4 Cancer / Karka)
        'KE'  => ['nakshatra' => 'Punarvasu', 'pada' => 1, 'rashi' => 'Mithun'],
        'KO'  => ['nakshatra' => 'Punarvasu', 'pada' => 2, 'rashi' => 'Mithun'],
        'HA'  => ['nakshatra' => 'Punarvasu', 'pada' => 3, 'rashi' => 'Mithun'],
        'HI'  => ['nakshatra' => 'Punarvasu', 'pada' => 4, 'rashi' => 'Karka'],
        'HEE' => ['nakshatra' => 'Punarvasu', 'pada' => 4, 'rashi' => 'Karka'],

        // 8. Pushya (Cancer / Karka)
        'HU'  => ['nakshatra' => 'Pushya', 'pada' => 1, 'rashi' => 'Karka'],
        'HOO' => ['nakshatra' => 'Pushya', 'pada' => 1, 'rashi' => 'Karka'],
        'HE'  => ['nakshatra' => 'Pushya', 'pada' => 2, 'rashi' => 'Karka'],
        'HO'  => ['nakshatra' => 'Pushya', 'pada' => 3, 'rashi' => 'Karka'],
        'DA'  => ['nakshatra' => 'Pushya', 'pada' => 4, 'rashi' => 'Karka'],

        // 9. Ashlesha (Cancer / Karka)
        'DI'  => ['nakshatra' => 'Ashlesha', 'pada' => 1, 'rashi' => 'Karka'],
        'DEE' => ['nakshatra' => 'Ashlesha', 'pada' => 1, 'rashi' => 'Karka'],
        'DU'  => ['nakshatra' => 'Ashlesha', 'pada' => 2, 'rashi' => 'Karka'],
        'DOO' => ['nakshatra' => 'Ashlesha', 'pada' => 2, 'rashi' => 'Karka'],
        'DE'  => ['nakshatra' => 'Ashlesha', 'pada' => 3, 'rashi' => 'Karka'],
        'DO'  => ['nakshatra' => 'Ashlesha', 'pada' => 4, 'rashi' => 'Karka'],

        // 10. Magha (Leo / Simha)
        'MA'  => ['nakshatra' => 'Magha', 'pada' => 1, 'rashi' => 'Simha'],
        'MI'  => ['nakshatra' => 'Magha', 'pada' => 2, 'rashi' => 'Simha'],
        'MEE' => ['nakshatra' => 'Magha', 'pada' => 2, 'rashi' => 'Simha'],
        'MU'  => ['nakshatra' => 'Magha', 'pada' => 3, 'rashi' => 'Simha'],
        'MOO' => ['nakshatra' => 'Magha', 'pada' => 3, 'rashi' => 'Simha'],
        'ME'  => ['nakshatra' => 'Magha', 'pada' => 4, 'rashi' => 'Simha'],

        // 11. Purva Phalguni (Leo / Simha)
        'MO'  => ['nakshatra' => 'Purva Phalguni', 'pada' => 1, 'rashi' => 'Simha'],
        'TA'  => ['nakshatra' => 'Purva Phalguni', 'pada' => 2, 'rashi' => 'Simha'],
        'TI'  => ['nakshatra' => 'Purva Phalguni', 'pada' => 3, 'rashi' => 'Simha'],
        'TEE' => ['nakshatra' => 'Purva Phalguni', 'pada' => 3, 'rashi' => 'Simha'],

        // 12. Uttara Phalguni (Pada 1 Leo, Pada 2-4 Virgo / Kanya)
        'TE'  => ['nakshatra' => 'Uttara Phalguni', 'pada' => 1, 'rashi' => 'Simha'],
        'TO'  => ['nakshatra' => 'Uttara Phalguni', 'pada' => 2, 'rashi' => 'Kanya'],
        'PA'  => ['nakshatra' => 'Uttara Phalguni', 'pada' => 3, 'rashi' => 'Kanya'],
        'PI'  => ['nakshatra' => 'Uttara Phalguni', 'pada' => 4, 'rashi' => 'Kanya'],
        'PEE' => ['nakshatra' => 'Uttara Phalguni', 'pada' => 4, 'rashi' => 'Kanya'],

        // 13. Hasta (Virgo / Kanya)
        'PU'  => ['nakshatra' => 'Hasta', 'pada' => 1, 'rashi' => 'Kanya'],
        'POO' => ['nakshatra' => 'Hasta', 'pada' => 1, 'rashi' => 'Kanya'],
        'SHA' => ['nakshatra' => 'Hasta', 'pada' => 2, 'rashi' => 'Kanya'],
        'NA'  => ['nakshatra' => 'Hasta', 'pada' => 3, 'rashi' => 'Kanya'],
        'THA' => ['nakshatra' => 'Hasta', 'pada' => 4, 'rashi' => 'Kanya'],

        // 14. Chitra (Pada 1-2 Virgo, Pada 3-4 Libra / Tula)
        'PE'  => ['nakshatra' => 'Chitra', 'pada' => 1, 'rashi' => 'Kanya'],
        'PO'  => ['nakshatra' => 'Chitra', 'pada' => 2, 'rashi' => 'Kanya'],
        'RA'  => ['nakshatra' => 'Chitra', 'pada' => 3, 'rashi' => 'Tula'],
        'RI'  => ['nakshatra' => 'Chitra', 'pada' => 4, 'rashi' => 'Tula'],
        'REE' => ['nakshatra' => 'Chitra', 'pada' => 4, 'rashi' => 'Tula'],

        // 15. Swati (Libra / Tula)
        'RU'  => ['nakshatra' => 'Swati', 'pada' => 1, 'rashi' => 'Tula'],
        'ROO' => ['nakshatra' => 'Swati', 'pada' => 1, 'rashi' => 'Tula'],
        'RE'  => ['nakshatra' => 'Swati', 'pada' => 2, 'rashi' => 'Tula'],
        'RO'  => ['nakshatra' => 'Swati', 'pada' => 3, 'rashi' => 'Tula'],
        'TAA' => ['nakshatra' => 'Swati', 'pada' => 4, 'rashi' => 'Tula'],

        // 16. Vishakha (Pada 1-3 Libra, Pada 4 Scorpio / Vrishchik)
        'THI' => ['nakshatra' => 'Vishakha', 'pada' => 1, 'rashi' => 'Tula'],
        'TU'  => ['nakshatra' => 'Vishakha', 'pada' => 2, 'rashi' => 'Tula'],
        'TOO' => ['nakshatra' => 'Vishakha', 'pada' => 2, 'rashi' => 'Tula'],
        'THE' => ['nakshatra' => 'Vishakha', 'pada' => 3, 'rashi' => 'Tula'],
        'THO' => ['nakshatra' => 'Vishakha', 'pada' => 4, 'rashi' => 'Vrishchik'],

        // 17. Anuradha (Scorpio / Vrishchik)
        'NI'  => ['nakshatra' => 'Anuradha', 'pada' => 2, 'rashi' => 'Vrishchik'],
        'NEE' => ['nakshatra' => 'Anuradha', 'pada' => 2, 'rashi' => 'Vrishchik'],
        'NU'  => ['nakshatra' => 'Anuradha', 'pada' => 3, 'rashi' => 'Vrishchik'],
        'NOO' => ['nakshatra' => 'Anuradha', 'pada' => 3, 'rashi' => 'Vrishchik'],
        'NE'  => ['nakshatra' => 'Anuradha', 'pada' => 4, 'rashi' => 'Vrishchik'],

        // 18. Jyeshtha (Scorpio / Vrishchik)
        'NO'  => ['nakshatra' => 'Jyeshtha', 'pada' => 1, 'rashi' => 'Vrishchik'],
        'YA'  => ['nakshatra' => 'Jyeshtha', 'pada' => 2, 'rashi' => 'Vrishchik'],
        'YI'  => ['nakshatra' => 'Jyeshtha', 'pada' => 3, 'rashi' => 'Vrishchik'],
        'YU'  => ['nakshatra' => 'Jyeshtha', 'pada' => 4, 'rashi' => 'Vrishchik'],

        // 19. Mula (Sagittarius / Dhanu)
        'YE'  => ['nakshatra' => 'Mula', 'pada' => 1, 'rashi' => 'Dhanu'],
        'YO'  => ['nakshatra' => 'Mula', 'pada' => 2, 'rashi' => 'Dhanu'],
        'BHA' => ['nakshatra' => 'Mula', 'pada' => 3, 'rashi' => 'Dhanu'],
        'BHI' => ['nakshatra' => 'Mula', 'pada' => 4, 'rashi' => 'Dhanu'],
        'BHEE'=> ['nakshatra' => 'Mula', 'pada' => 4, 'rashi' => 'Dhanu'],

        // 20. Purva Ashadha (Sagittarius / Dhanu)
        'BHU' => ['nakshatra' => 'Purva Ashadha', 'pada' => 1, 'rashi' => 'Dhanu'],
        'DHA' => ['nakshatra' => 'Purva Ashadha', 'pada' => 2, 'rashi' => 'Dhanu'],
        'PHA' => ['nakshatra' => 'Purva Ashadha', 'pada' => 3, 'rashi' => 'Dhanu'],
        'DHADHA' => ['nakshatra' => 'Purva Ashadha', 'pada' => 4, 'rashi' => 'Dhanu'],

        // 21. Uttara Ashadha (Pada 1 Sagittarius, Pada 2-4 Capricorn / Makar)
        'BHE' => ['nakshatra' => 'Uttara Ashadha', 'pada' => 1, 'rashi' => 'Dhanu'],
        'BHO' => ['nakshatra' => 'Uttara Ashadha', 'pada' => 2, 'rashi' => 'Makar'],
        'JA'  => ['nakshatra' => 'Uttara Ashadha', 'pada' => 3, 'rashi' => 'Makar'],
        'JI'  => ['nakshatra' => 'Uttara Ashadha', 'pada' => 4, 'rashi' => 'Makar'],
        'JEE' => ['nakshatra' => 'Uttara Ashadha', 'pada' => 4, 'rashi' => 'Makar'],

        // 22. Shravana (Capricorn / Makar)
        'JU'  => ['nakshatra' => 'Shravana', 'pada' => 1, 'rashi' => 'Makar'],
        'JOO' => ['nakshatra' => 'Shravana', 'pada' => 1, 'rashi' => 'Makar'],
        'JE'  => ['nakshatra' => 'Shravana', 'pada' => 2, 'rashi' => 'Makar'],
        'JO'  => ['nakshatra' => 'Shravana', 'pada' => 3, 'rashi' => 'Makar'],
        'KHI' => ['nakshatra' => 'Shravana', 'pada' => 4, 'rashi' => 'Makar'],

        // 23. Dhanishta (Pada 1-2 Capricorn, Pada 3-4 Aquarius / Kumbh)
        'GA'  => ['nakshatra' => 'Dhanishta', 'pada' => 1, 'rashi' => 'Makar'],
        'GI'  => ['nakshatra' => 'Dhanishta', 'pada' => 2, 'rashi' => 'Makar'],
        'GU'  => ['nakshatra' => 'Dhanishta', 'pada' => 3, 'rashi' => 'Kumbh'],
        'GOO' => ['nakshatra' => 'Dhanishta', 'pada' => 3, 'rashi' => 'Kumbh'],
        'GE'  => ['nakshatra' => 'Dhanishta', 'pada' => 4, 'rashi' => 'Kumbh'],

        // 24. Shatabhisha (Aquarius / Kumbh)
        'GO'  => ['nakshatra' => 'Shatabhisha', 'pada' => 1, 'rashi' => 'Kumbh'],
        'SA'  => ['nakshatra' => 'Shatabhisha', 'pada' => 2, 'rashi' => 'Kumbh'],
        'SI'  => ['nakshatra' => 'Shatabhisha', 'pada' => 3, 'rashi' => 'Kumbh'],
        'SEE' => ['nakshatra' => 'Shatabhisha', 'pada' => 3, 'rashi' => 'Kumbh'],
        'SU'  => ['nakshatra' => 'Shatabhisha', 'pada' => 4, 'rashi' => 'Kumbh'],
        'SOO' => ['nakshatra' => 'Shatabhisha', 'pada' => 4, 'rashi' => 'Kumbh'],

        // 25. Purva Bhadrapada (Pada 1-3 Aquarius, Pada 4 Pisces / Meen)
        'SE'  => ['nakshatra' => 'Purva Bhadrapada', 'pada' => 1, 'rashi' => 'Kumbh'],
        'SO'  => ['nakshatra' => 'Purva Bhadrapada', 'pada' => 2, 'rashi' => 'Kumbh'],
        'THAA'=> ['nakshatra' => 'Purva Bhadrapada', 'pada' => 3, 'rashi' => 'Kumbh'],

        // 26. Uttara Bhadrapada (Pisces / Meen)
        'JHA' => ['nakshatra' => 'Uttara Bhadrapada', 'pada' => 3, 'rashi' => 'Meen'],

        // 27. Revati (Pisces / Meen)
        'CHA' => ['nakshatra' => 'Revati', 'pada' => 3, 'rashi' => 'Meen'],
        'CHI' => ['nakshatra' => 'Revati', 'pada' => 4, 'rashi' => 'Meen'],
        'CHEE'=> ['nakshatra' => 'Revati', 'pada' => 4, 'rashi' => 'Meen']
    ];

    /**
     * Rashi Astrological Metadata & Auspicious Attributes
     */
    private $rashi_meta = [
        'Mesh' => [
            'name'                => 'Mesh (Aries)',
            'hindi'               => 'मेष',
            'ruling_planet'       => 'Mars (Mangal)',
            'vedic_element'       => 'Fire (Agni)',
            'rashi_deity'         => 'Lord Kartikeya / Lord Shiva',
            'favorable_signs'     => 'Highly favorable for fiery (Leo, Sagittarius) and air signs (Gemini, Aquarius).',
            'lucky_numbers'       => [9, 1, 3],
            'lucky_colors'        => ['Bright Red', 'Coral', 'Scarlet', 'Golden'],
            'lucky_days'          => ['Tuesday', 'Sunday'],
            'lucky_gemstone'      => 'Red Coral (Moonga)',
            'lucky_metal'         => 'Copper and Gold',
            'favorable_direction' => 'East'
        ],
        'Vrishabh' => [
            'name'                => 'Vrishabh (Taurus)',
            'hindi'               => 'वृषभ',
            'ruling_planet'       => 'Venus (Shukra)',
            'vedic_element'       => 'Earth (Prithvi)',
            'rashi_deity'         => 'Goddess Lakshmi',
            'favorable_signs'     => 'Highly harmonious with earth (Virgo, Capricorn) and water signs (Cancer, Pisces).',
            'lucky_numbers'       => [6, 5, 8],
            'lucky_colors'        => ['White', 'Pastel Pink', 'Lotus Pink', 'Cream'],
            'lucky_days'          => ['Friday', 'Wednesday'],
            'lucky_gemstone'      => 'Diamond (Heera) / White Sapphire',
            'lucky_metal'         => 'Silver and Platinum',
            'favorable_direction' => 'South-East'
        ],
        'Mithun' => [
            'name'                => 'Mithun (Gemini)',
            'hindi'               => 'मिथुन',
            'ruling_planet'       => 'Mercury (Budha)',
            'vedic_element'       => 'Air (Vayu)',
            'rashi_deity'         => 'Lord Vishnu / Lord Ganesha',
            'favorable_signs'     => 'Exceptionally compatible with air (Libra, Aquarius) and fire signs (Aries, Leo).',
            'lucky_numbers'       => [5, 3, 1],
            'lucky_colors'        => ['Emerald Green', 'Light Green', 'Bright Yellow'],
            'lucky_days'          => ['Wednesday', 'Thursday'],
            'lucky_gemstone'      => 'Emerald (Panna)',
            'lucky_metal'         => 'Bronze and Gold',
            'favorable_direction' => 'North'
        ],
        'Karka' => [
            'name'                => 'Karka (Cancer)',
            'hindi'               => 'कर्क',
            'ruling_planet'       => 'Moon (Chandra)',
            'vedic_element'       => 'Water (Jal)',
            'rashi_deity'         => 'Lord Shiva / Goddess Parvati',
            'favorable_signs'     => 'Deep emotional resonance with water (Scorpio, Pisces) and supportive earth signs.',
            'lucky_numbers'       => [2, 7, 9],
            'lucky_colors'        => ['Pearl White', 'Silver', 'Moonlit Cream', 'Light Blue'],
            'lucky_days'          => ['Monday', 'Thursday'],
            'lucky_gemstone'      => 'Natural Pearl (Moti) / Moonstone',
            'lucky_metal'         => 'Silver',
            'favorable_direction' => 'North-West'
        ],
        'Simha' => [
            'name'                => 'Simha (Leo)',
            'hindi'               => 'सिंह',
            'ruling_planet'       => 'Sun (Surya)',
            'vedic_element'       => 'Fire (Agni)',
            'rashi_deity'         => 'Lord Surya / Lord Vishnu',
            'favorable_signs'     => 'Natural leadership resonance with fire (Aries, Sagittarius) and air signs (Libra, Gemini).',
            'lucky_numbers'       => [1, 5, 9],
            'lucky_colors'        => ['Gold', 'Radiant Orange', 'Royal Crimson', 'Saffron'],
            'lucky_days'          => ['Sunday', 'Tuesday'],
            'lucky_gemstone'      => 'Natural Ruby (Manikya)',
            'lucky_metal'         => 'Gold and Copper',
            'favorable_direction' => 'East'
        ],
        'Kanya' => [
            'name'                => 'Kanya (Virgo)',
            'hindi'               => 'कन्या',
            'ruling_planet'       => 'Mercury (Budha)',
            'vedic_element'       => 'Earth (Prithvi)',
            'rashi_deity'         => 'Lord Ganesha',
            'favorable_signs'     => 'Grounded affinity with earth (Taurus, Capricorn) and receptive water signs.',
            'lucky_numbers'       => [5, 2, 6],
            'lucky_colors'        => ['Dark Green', 'Olive', 'Jade', 'White'],
            'lucky_days'          => ['Wednesday', 'Friday'],
            'lucky_gemstone'      => 'Emerald (Panna) / Peridot',
            'lucky_metal'         => 'Bronze and Silver',
            'favorable_direction' => 'South'
        ],
        'Tula' => [
            'name'                => 'Tula (Libra)',
            'hindi'               => 'तुला',
            'ruling_planet'       => 'Venus (Shukra)',
            'vedic_element'       => 'Air (Vayu)',
            'rashi_deity'         => 'Goddess Lakshmi',
            'favorable_signs'     => 'Highly favorable for airy (Gemini, Aquarius) and energetic fire signs (Leo, Sagittarius).',
            'lucky_numbers'       => [6, 3, 9],
            'lucky_colors'        => ['Diamond White', 'Light Sky Blue', 'Silver', 'Pastel Rose'],
            'lucky_days'          => ['Friday', 'Wednesday'],
            'lucky_gemstone'      => 'Diamond / White Sapphire (Heera / Safed Pukhraj)',
            'lucky_metal'         => 'Silver and White Gold',
            'favorable_direction' => 'West'
        ],
        'Vrishchik' => [
            'name'                => 'Vrishchik (Scorpio)',
            'hindi'               => 'वृश्चिक',
            'ruling_planet'       => 'Mars (Mangal)',
            'vedic_element'       => 'Water (Jal)',
            'rashi_deity'         => 'Lord Kartikeya / Lord Hanuman',
            'favorable_signs'     => 'Profound synergy with water (Cancer, Pisces) and stabilizing earth signs (Virgo, Capricorn).',
            'lucky_numbers'       => [9, 1, 4],
            'lucky_colors'        => ['Deep Crimson', 'Maroon', 'Rust Red', 'Black'],
            'lucky_days'          => ['Tuesday', 'Sunday'],
            'lucky_gemstone'      => 'Red Coral (Moonga)',
            'lucky_metal'         => 'Copper and Iron',
            'favorable_direction' => 'North'
        ],
        'Dhanu' => [
            'name'                => 'Dhanu (Sagittarius)',
            'hindi'               => 'धनु',
            'ruling_planet'       => 'Jupiter (Brihaspati / Guru)',
            'vedic_element'       => 'Fire (Agni)',
            'rashi_deity'         => 'Lord Vishnu / Lord Dakshinamurthy',
            'favorable_signs'     => 'Expansive alignment with fiery (Aries, Leo) and intellectual air signs (Libra, Aquarius).',
            'lucky_numbers'       => [3, 9, 7],
            'lucky_colors'        => ['Bright Yellow', 'Saffron', 'Golden Honey', 'Royal Purple'],
            'lucky_days'          => ['Thursday', 'Sunday'],
            'lucky_gemstone'      => 'Yellow Sapphire (Pukhraj)',
            'lucky_metal'         => 'Gold and Brass',
            'favorable_direction' => 'North-East'
        ],
        'Makar' => [
            'name'                => 'Makar (Capricorn)',
            'hindi'               => 'मकर',
            'ruling_planet'       => 'Saturn (Shani)',
            'vedic_element'       => 'Earth (Prithvi)',
            'rashi_deity'         => 'Lord Shani / Lord Shiva',
            'favorable_signs'     => 'Disciplined alignment with earth signs (Taurus, Virgo) and intuitive water signs.',
            'lucky_numbers'       => [8, 4, 6],
            'lucky_colors'        => ['Navy Blue', 'Royal Indigo', 'Steel Grey', 'Charcoal'],
            'lucky_days'          => ['Saturday', 'Wednesday'],
            'lucky_gemstone'      => 'Blue Sapphire (Neelam) / Amethyst',
            'lucky_metal'         => 'Iron and Lead',
            'favorable_direction' => 'South'
        ],
        'Kumbh' => [
            'name'                => 'Kumbha (Aquarius)',
            'hindi'               => 'कुंभ',
            'ruling_planet'       => 'Saturn (Shani)',
            'vedic_element'       => 'Air (Vayu)',
            'rashi_deity'         => 'Lord Shiva',
            'favorable_signs'     => 'Visionary harmony with air (Gemini, Libra) and visionary fire signs (Aries, Sagittarius).',
            'lucky_numbers'       => [8, 4, 5],
            'lucky_colors'        => ['Electric Blue', 'Cyan', 'Ultramarine', 'Violet'],
            'lucky_days'          => ['Saturday', 'Friday'],
            'lucky_gemstone'      => 'Blue Sapphire (Neelam) / Hessonite (Gomed)',
            'lucky_metal'         => 'Iron and Platinum',
            'favorable_direction' => 'West'
        ],
        'Meen' => [
            'name'                => 'Meen (Pisces)',
            'hindi'               => 'मीन',
            'ruling_planet'       => 'Jupiter (Brihaspati / Guru)',
            'vedic_element'       => 'Water (Jal)',
            'rashi_deity'         => 'Lord Vishnu',
            'favorable_signs'     => 'Spiritual resonance with water signs (Cancer, Scorpio) and nurturing earth signs.',
            'lucky_numbers'       => [3, 7, 1],
            'lucky_colors'        => ['Sea Green', 'Aquamarine', 'Pale Yellow', 'Lavender'],
            'lucky_days'          => ['Thursday', 'Monday'],
            'lucky_gemstone'      => 'Yellow Sapphire (Pukhraj) / Natural Pearl',
            'lucky_metal'         => 'Gold and Bronze',
            'favorable_direction' => 'North-East'
        ]
    ];

    /**
     * Singleton Instance
     */
    public static function instance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct()
    {
        // Auto-provision table on initialization if missing (ensures live deployment safety)
        $this->ensure_profiles_table();
        $this->ensure_snippet_delegation();
    }

    /**
     * Auto-patches Snippet 74 (Baby Names Engine) in Code Snippets database table
     * to ensure live server seamlessly delegates name_detail rendering to MR_Baby_Template_Renderer.
     */
    public function ensure_snippet_delegation()
    {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }

        // Fast check using transient to avoid queries on every request
        if (get_transient('mr_baby_snippet_delegation_v1')) {
            return;
        }

        $table = $wpdb->prefix . 'snippets';
        if ($wpdb->get_var("SHOW TABLES LIKE '{$table}'") !== $table) {
            set_transient('mr_baby_snippet_delegation_v1', 1, DAY_IN_SECONDS);
            return;
        }

        $snippet = $wpdb->get_row("SELECT id, code FROM {$table} WHERE name LIKE '%Baby Names Engine%' OR id = 74 LIMIT 1");
        if (!$snippet || empty($snippet->code)) {
            set_transient('mr_baby_snippet_delegation_v1', 1, DAY_IN_SECONDS);
            return;
        }

        if (strpos($snippet->code, 'MR_Baby_Template_Renderer') === false) {
            $needle = 'private function render_name_detail()';
            if (strpos($snippet->code, $needle) !== false) {
                $replacement = "private function render_name_detail()\n    {\n        if (class_exists('MR_Baby_Template_Renderer')) {\n            \$name_slug = get_query_var('baby_name');\n            \$gender = get_query_var('gender') ?: 'boy';\n            \$char = get_query_var('alphabet') ?: substr(\$name_slug, 0, 1);\n            \$religion = get_query_var('religion') ?: (\$_GET['religion'] ?? 'Hindu');\n            echo MR_Baby_Template_Renderer::render_detail(\$name_slug, \$gender, \$char, \$religion);\n            return;\n        }";

                $new_code = preg_replace('/private\s+function\s+render_name_detail\s*\(\)\s*\{/i', $replacement, $snippet->code, 1);
                if (!empty($new_code) && $new_code !== $snippet->code) {
                    $wpdb->update($table, ['code' => $new_code], ['id' => $snippet->id]);
                }
            }
        }

        set_transient('mr_baby_snippet_delegation_v1', 1, DAY_IN_SECONDS);
    }

    /**
     * Ensures baby_name_profiles table exists across all environments (Local, Staging, Live)
     */
    public function ensure_profiles_table()
    {
        global $wpdb;
        if (empty($wpdb)) {
            return;
        }
        $table = $wpdb->prefix . 'baby_name_profiles';

        // Fast check using transient to avoid SHOW TABLES on every page request
        if (get_transient('mr_baby_profiles_table_verified')) {
            return;
        }

        $exists = $wpdb->get_var("SHOW TABLES LIKE '{$table}'");
        if ($exists !== $table) {
            $charset_collate = $wpdb->get_charset_collate();
            $sql = "CREATE TABLE IF NOT EXISTS `{$table}` (
                `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
                `baby_name_id` BIGINT UNSIGNED NOT NULL,
                `name_slug` VARCHAR(255) NOT NULL,
                `gender` VARCHAR(20) NOT NULL,
                `religion` VARCHAR(100) DEFAULT 'Hindu',
                `rashi` VARCHAR(100) DEFAULT NULL,
                `nakshatra` VARCHAR(100) DEFAULT NULL,
                `numerology_number` INT DEFAULT NULL,
                `profile_json` LONGTEXT NOT NULL,
                `word_count` INT DEFAULT 0,
                `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                UNIQUE KEY `idx_baby_name_id` (`baby_name_id`),
                KEY `idx_slug` (`name_slug`),
                KEY `idx_rel_gender` (`religion`, `gender`)
            ) ENGINE=InnoDB {$charset_collate};";

            require_once ABSPATH . 'wp-admin/includes/upgrade.php';
            dbDelta($sql);
        }

        set_transient('mr_baby_profiles_table_verified', 1, DAY_IN_SECONDS);
    }

    /**
     * Computes the complete deterministic Vedic Astrology and Numerology dataset for any name.
     *
     * @param string $name Raw baby name (e.g. "Tushar")
     * @param string $gender Gender ("Boy", "Girl", "Unisex")
     * @param string $religion Religion ("Hindu", "Muslim", "Christian", "Sikh")
     * @return array Standardized master astro-numerology profile
     */
    public function compute_astro_profile($name, $gender = 'Boy', $religion = 'Hindu')
    {
        $clean_name = trim($name);

        // 1. Vedic Nakshatra, Pada & Rashi
        $nakshatra_data = $this->get_nakshatra_and_pada($clean_name);
        $rashi_key      = $nakshatra_data['rashi_key'];
        $rashi_info     = $this->rashi_meta[$rashi_key] ?? $this->rashi_meta['Tula'];

        // 2. Numerology Core (Chaldean & Pythagorean)
        $chaldean_calc    = $this->get_chaldean_number($clean_name);
        $pythagorean_calc = $this->get_pythagorean_number($clean_name);
        $soul_urge        = $this->get_soul_urge_number($clean_name);
        $personality_num  = $this->get_personality_number($clean_name);
        $life_path_res    = $this->get_life_path_resonance($chaldean_calc['reduced'], $pythagorean_calc['reduced']);

        return [
            'name'                 => ucfirst(strtolower($clean_name)),
            'gender'               => ucfirst(strtolower($gender)),
            'religion'             => ucfirst(strtolower($religion)),
            'vedic_astrology'      => [
                'rashi_moon_sign'             => $rashi_info['name'],
                'rashi_hindi'                 => $rashi_info['hindi'],
                'ruling_planet'               => $rashi_info['ruling_planet'],
                'nakshatra'                   => $nakshatra_data['nakshatra'],
                'nakshatra_pada'              => $nakshatra_data['pada'],
                'vedic_element'               => $rashi_info['vedic_element'],
                'auspicious_starting_syllable'=> $nakshatra_data['syllable'] . '-',
                'rashi_deity'                 => $rashi_info['rashi_deity'],
                'favorable_rashis_and_elements'=> $rashi_info['favorable_signs']
            ],
            'numerology'           => [
                'chaldean_number'     => $chaldean_calc['reduced'],
                'chaldean_compound'   => $chaldean_calc['compound'],
                'pythagorean_number'  => $pythagorean_calc['reduced'],
                'expression_number'   => $chaldean_calc['reduced'],
                'soul_urge_number'    => $soul_urge,
                'personality_number'  => $personality_num,
                'life_path_resonance' => $life_path_res,
                'meanings'            => self::get_numerology_meanings()
            ],
            'auspicious_attributes'=> [
                'lucky_numbers'       => $rashi_info['lucky_numbers'],
                'lucky_colors'        => $rashi_info['lucky_colors'],
                'lucky_days'          => $rashi_info['lucky_days'],
                'lucky_gemstone'      => $rashi_info['lucky_gemstone'],
                'lucky_metal'         => $rashi_info['lucky_metal'],
                'favorable_direction' => $rashi_info['favorable_direction']
            ]
        ];
    }

    /**
     * Resolves Nakshatra, Pada and Rashi deterministically using the 108 Pada sound matrix.
     * Evaluates longest prefix matches down to single letters with sound fallbacks.
     */
    public function get_nakshatra_and_pada($name)
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));

        if (empty($normalized)) {
            return [
                'nakshatra' => 'Ashwini',
                'pada'      => 1,
                'syllable'  => 'A',
                'rashi_key' => 'Mesh'
            ];
        }

        // Try prefixes from length 6 down to 1
        $len = strlen($normalized);
        $max_prefix = min($len, 6);

        for ($p = $max_prefix; $p >= 1; $p--) {
            $prefix = substr($normalized, 0, $p);
            if (isset($this->pada_matrix[$prefix])) {
                $match = $this->pada_matrix[$prefix];
                return [
                    'nakshatra' => $match['nakshatra'],
                    'pada'      => $match['pada'],
                    'syllable'  => ucfirst(strtolower($prefix)),
                    'rashi_key' => $match['rashi']
                ];
            }
        }

        // Phonetic fallback mapping based on first letter
        $first_letter = substr($normalized, 0, 1);
        $letter_to_rashi = [
            'A' => ['Mesh', 'Ashwini', 1],
            'B' => ['Vrishabh', 'Rohini', 2],
            'C' => ['Mithun', 'Mrigashira', 3],
            'D' => ['Karka', 'Pushya', 4],
            'E' => ['Vrishabh', 'Krittika', 2],
            'F' => ['Dhanu', 'Purva Ashadha', 3],
            'G' => ['Kumbh', 'Dhanishta', 3],
            'H' => ['Karka', 'Punarvasu', 4],
            'I' => ['Mesh', 'Ashwini', 1],
            'J' => ['Makar', 'Uttara Ashadha', 3],
            'K' => ['Mithun', 'Ardra', 1],
            'L' => ['Mesh', 'Ashwini', 4],
            'M' => ['Simha', 'Magha', 1],
            'N' => ['Vrishchik', 'Anuradha', 1],
            'O' => ['Vrishabh', 'Rohini', 1],
            'P' => ['Kanya', 'Uttara Phalguni', 3],
            'Q' => ['Mithun', 'Mrigashira', 3],
            'R' => ['Tula', 'Chitra', 3],
            'S' => ['Kumbh', 'Shatabhisha', 2],
            'T' => ['Tula', 'Vishakha', 2],
            'U' => ['Vrishabh', 'Krittika', 3],
            'V' => ['Vrishabh', 'Rohini', 2],
            'W' => ['Vrishabh', 'Rohini', 4],
            'X' => ['Mithun', 'Ardra', 1],
            'Y' => ['Dhanu', 'Mula', 1],
            'Z' => ['Meen', 'Revati', 3]
        ];

        if (isset($letter_to_rashi[$first_letter])) {
            $f = $letter_to_rashi[$first_letter];
            return [
                'nakshatra' => $f[1],
                'pada'      => $f[2],
                'syllable'  => $first_letter,
                'rashi_key' => $f[0]
            ];
        }

        return [
            'nakshatra' => 'Swati',
            'pada'      => 1,
            'syllable'  => $first_letter,
            'rashi_key' => 'Tula'
        ];
    }

    /**
     * Calculates Chaldean compound and single reduced number
     */
    public function get_chaldean_number($name)
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $sum = 0;
        $len = strlen($normalized);

        for ($i = 0; $i < $len; $i++) {
            $char = $normalized[$i];
            if (isset($this->chaldean_map[$char])) {
                $sum += $this->chaldean_map[$char];
            }
        }

        return [
            'compound' => $sum,
            'reduced'  => $this->reduce_to_single($sum)
        ];
    }

    /**
     * Calculates Pythagorean (Western) compound and single reduced number
     */
    public function get_pythagorean_number($name)
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $sum = 0;
        $len = strlen($normalized);

        for ($i = 0; $i < $len; $i++) {
            $char = $normalized[$i];
            if (isset($this->pythagorean_map[$char])) {
                $sum += $this->pythagorean_map[$char];
            }
        }

        return [
            'compound' => $sum,
            'reduced'  => $this->reduce_to_single($sum)
        ];
    }

    /**
     * Calculates Soul Urge (Heart's Desire) number from vowels (A, E, I, O, U)
     */
    public function get_soul_urge_number($name)
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $vowels = ['A' => true, 'E' => true, 'I' => true, 'O' => true, 'U' => true];
        $sum = 0;
        $len = strlen($normalized);

        for ($i = 0; $i < $len; $i++) {
            $char = $normalized[$i];
            if (isset($vowels[$char]) && isset($this->chaldean_map[$char])) {
                $sum += $this->chaldean_map[$char];
            }
        }

        return $this->reduce_to_single($sum);
    }

    /**
     * Calculates Personality (Outer Expression) number from consonants
     */
    public function get_personality_number($name)
    {
        $normalized = strtoupper(preg_replace('/[^A-Za-z]/', '', $name));
        $vowels = ['A' => true, 'E' => true, 'I' => true, 'O' => true, 'U' => true];
        $sum = 0;
        $len = strlen($normalized);

        for ($i = 0; $i < $len; $i++) {
            $char = $normalized[$i];
            if (!isset($vowels[$char]) && isset($this->chaldean_map[$char])) {
                $sum += $this->chaldean_map[$char];
            }
        }

        return $this->reduce_to_single($sum);
    }

    /**
     * Numerological digit sum reduction to single digit 1-9
     */
    public function reduce_to_single($number)
    {
        if ($number <= 0) {
            return 1;
        }
        while ($number > 9) {
            $number = array_sum(str_split((string)$number));
        }
        return (int)$number;
    }

    /**
     * Deterministic Life Path Resonance narrative
     */
    private function get_life_path_resonance($chaldean, $pythagorean)
    {
        $meanings = [
            1 => 'governed by the Sun, radiating leadership, pioneering spirit, and executive autonomy.',
            2 => 'governed by the Moon, emphasizing diplomatic grace, intuitive empathy, and peacemaking.',
            3 => 'governed by Jupiter, magnifying creative expression, optimism, and social charisma.',
            4 => 'governed by Rahu/Uranus, embodying structural precision, perseverance, and pragmatic discipline.',
            5 => 'governed by Mercury, vibrating with adaptability, communicative intellect, and dynamic curiosity.',
            6 => 'governed by Venus, anchoring harmonious nurturing, domestic responsibility, and refined aesthetic taste.',
            7 => 'governed by Ketu/Neptune, fostering philosophical depth, contemplative learning, and spiritual intellect.',
            8 => 'governed by Saturn, catalyzing karmic mastery, executive materialization, and enduring authority.',
            9 => 'governed by Mars, inspiring humanitarian vision, fearless courage, and universal compassion.'
        ];

        $ch_text = $meanings[$chaldean] ?? $meanings[1];
        $py_text = $meanings[$pythagorean] ?? $meanings[6];

        return "Holds a primary Chaldean vibration of {$chaldean} ({$ch_text}) synthesized with an Expression number of {$pythagorean} ({$py_text}) to produce balanced destiny flow.";
    }

    /**
     * Returns rashi metadata by key
     */
    public function get_rashi_meta($rashi_key)
    {
        return $this->rashi_meta[$rashi_key] ?? null;
    }

    /**
     * Helper to return all 12 Rashis
     */
    public function get_all_rashis()
    {
        return $this->rashi_meta;
    }

    /**
     * Concise one-liner definitions for each numerological marker
     */
    public static function get_numerology_meanings()
    {
        return [
            'chaldean_number'    => 'Destiny vibration based on Vedic-Chaldean sound frequencies, revealing core spiritual and life path purpose.',
            'chaldean_compound'  => 'Double-digit karmic sum reflecting subconscious strengths, occult lessons, and latent potentials.',
            'pythagorean_number' => 'Western vibrational frequency reflecting outer worldly perception, mindset, and natural gifts.',
            'expression_number'  => 'Channels individual talents and faculties into tangible vocational and life achievements.',
            'soul_urge_number'   => 'Derived from name vowels, exposing the heart’s innermost passions and emotional aspirations.',
            'personality_number' => 'Derived from name consonants, projecting the outer persona, charisma, and public first impression.'
        ];
    }
}
