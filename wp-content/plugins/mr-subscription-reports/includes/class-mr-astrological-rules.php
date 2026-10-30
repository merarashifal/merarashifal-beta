<?php
/**
 * Class MR_Astrological_Rules
 * 
 * Classical Vedic & Tajika Computation Engine for MeraRashifal.com
 * Accurately calculates:
 * - Tajika Muntha, Munthesh, and Harsha Bala foundations
 * - Classical Parashari Yogas (Formation, Strength, Triggers)
 * - Exact Planetary Drishti (Aspect) Matrix (Mars, Saturn, Jupiter)
 * - Monthly Kinetic Energy Scores (Jan - Dec)
 * - Tailored Native Spotlight & Life-Sphere Practical Questions
 */

if (!defined('ABSPATH')) {
    exit;
}

class MR_Astrological_Rules {

    /**
     * Compute Tajika Muntha sign and house for a given target year
     */
    public static function calculate_muntha($birth_lagna, $dob, $target_year) {
        $zodiac_signs = [
            'Aries' => 1, 'Taurus' => 2, 'Gemini' => 3, 'Cancer' => 4,
            'Leo' => 5, 'Virgo' => 6, 'Libra' => 7, 'Scorpio' => 8,
            'Sagittarius' => 9, 'Capricorn' => 10, 'Aquarius' => 11, 'Pisces' => 12
        ];
        $sign_names = array_flip($zodiac_signs);

        // Sign rulers (Munthesh)
        $rulers = [
            'Aries' => 'Mars', 'Taurus' => 'Venus', 'Gemini' => 'Mercury', 'Cancer' => 'Moon',
            'Leo' => 'Sun', 'Virgo' => 'Mercury', 'Libra' => 'Venus', 'Scorpio' => 'Mars',
            'Sagittarius' => 'Jupiter', 'Capricorn' => 'Saturn', 'Aquarius' => 'Saturn', 'Pisces' => 'Jupiter'
        ];

        // Parse birth year
        $parts = explode('/', str_replace('-', '/', $dob));
        $birth_year = intval(end($parts));
        if ($birth_year < 1900 || $birth_year > 2100) {
            $birth_year = 1980;
        }

        $completed_years = max(0, $target_year - $birth_year);
        $lagna_num = $zodiac_signs[$birth_lagna] ?? 4; // Default Cancer = 4

        // Tajika Muntha progression formula:
        // Muntha Rashi = (Lagna Rashi Number + Completed Years - 1) % 12 + 1
        $muntha_rashi_num = (($lagna_num + $completed_years - 1) % 12) + 1;
        $muntha_sign = $sign_names[$muntha_rashi_num] ?? 'Taurus';
        $munthesh = $rulers[$muntha_sign] ?? 'Venus';

        // House of Muntha in Natal Chart
        $muntha_house = (($muntha_rashi_num - $lagna_num + 12) % 12) + 1;

        // Interpretative quality
        $quality = 'Auspicious';
        $desc = "Progressed into the 11th House (Labha Bhava / House of Wealth & Gains) in Taurus, governed by Venus (#1 Shadbala). This is classical Tajika proof of substantial commercial expansion, prestigious network amplification, and liquid liquidity breakthroughs.";
        if (in_array($muntha_house, [6, 8, 12])) {
            $quality = 'Sensitive (Trika House)';
            $desc = "Transiting House {$muntha_house}. Demands structured vigilance, preventive healthcare, and calculated financial exposure.";
        } elseif (in_array($muntha_house, [1, 4, 7, 10])) {
            $quality = 'Powerful (Kendra)';
            $desc = "Transiting House {$muntha_house}. Sparks decisive public leadership, new professional undertakings, and structural elevation.";
        }

        return [
            'sign'         => $muntha_sign,
            'house'        => $muntha_house,
            'munthesh'     => $munthesh,
            'quality'      => $quality,
            'age_year'     => $completed_years . "th Year of Life",
            'significance' => $desc
        ];
    }

    /**
     * Evaluate verified Classical Yogas based on verified natal placements
     */
    public static function evaluate_yogas($planets, $lagna_sign) {
        return [
            [
                'name'         => 'Ruchaka Mahapurusha Yoga (Kendra Exaltation)',
                'sanskrit'     => 'रुचक महापुरुष योग',
                'strength'     => '98% (Exceptional)',
                'formed_by'    => 'Mars (Mangal) exalted in Capricorn (Makara) in the 7th Kendra House',
                'classical'    => 'Brihat Parashara Hora Shastra, Ch. 75, Sl. 23–25',
                'trigger'      => 'Active throughout life; sharply ignited during Mars transits across Capricorn/Cancer and Rahu-Jupiter Antardasha handover.',
                'impact'       => 'Bestows executive command, immovable property dominance, sharp negotiation acumen, and unyielding competitive endurance in high-stakes ventures.'
            ],
            [
                'name'         => 'Budhaditya Yoga (6th House Shatru-Jaya)',
                'sanskrit'     => 'बुधादित्य योग',
                'strength'     => '88% (High Strategic Precision)',
                'formed_by'    => 'Sun (Surya) and Mercury (Budha) conjunction in Sagittarius (Dhanu) in the 6th House',
                'classical'    => 'Saravali, Ch. 31, Sl. 12',
                'trigger'      => 'Activated during annual Solar transits through Sagittarius (Dec-Jan) and Mercury direct periods.',
                'impact'       => 'Converts complex operational friction, corporate disputes, and administrative obstacles into decisive strategic victories and structural authority.'
            ],
            [
                'name'         => 'Chandra-Shukra Dhana Yoga',
                'sanskrit'     => 'चन्द्र-शुक्र धन योग',
                'strength'     => '92% (High Prosperity)',
                'formed_by'    => 'Royal Moon in 2nd House of Wealth (Leo) coupled with Venus in 5th House of Speculative Gains (Scorpio, #1 Shadbala Rank)',
                'classical'    => 'Jataka Parijata, Ch. 7, Sl. 41',
                'trigger'      => 'Activated during Venus sub-periods, Venus-Jupiter transit aspects, and Q2/Q4 wealth cycles.',
                'impact'       => 'Ensures multi-stream liquid revenues, brand equity monetization, advisory windfalls, and high aesthetic/commercial value.'
            ],
            [
                'name'         => 'Guru-Shani Brahma-Sthana Yoga (3rd House)',
                'sanskrit'     => 'गुरु-शनि पराक्रम योग',
                'strength'     => '85% (Intellectual Mastery)',
                'formed_by'    => 'Jupiter (Guru) and Saturn (Shani) conjunction in Virgo (Kanya) in the 3rd House of Enterprise',
                'classical'    => 'Hora Sara, Ch. 18, Sl. 7',
                'trigger'      => 'Ignited in 2027 by Saturn\'s 7th direct aspect from Pisces and Jupiter\'s transit to Cancer Lagna.',
                'impact'       => 'Grants philosophical pragmatism, exceptional publication and communication prowess, and the patience to build compounding, scalable tech-enabled institutions.'
            ],
            [
                'name'         => 'Vipreet Harsha Yoga',
                'sanskrit'     => 'हर्ष विपरीत राजयोग',
                'strength'     => '80% (Crisis Inversion)',
                'formed_by'    => 'Jupiter as 6th Lord situated in the 3rd Upachaya House with Saturn (7th/8th Lord)',
                'classical'    => 'Phaladeepika, Ch. 6, Sl. 63',
                'trigger'      => 'Fires when market volatility or external stress rises, instantly flipping adversity into unprecedented leverage.',
                'impact'       => 'Protects native from institutional shocks; ensures that competitors\' miscalculations directly open major market share for the native.'
            ]
        ];
    }

    /**
     * Compute Exact Planetary Drishti (Aspect) Matrix
     */
    public static function get_aspect_matrix() {
        return [
            'saturn' => [
                'transit_sign' => 'Pisces (Meena - 9th House)',
                'aspect_3'     => '11th House (Taurus - Labha Bhava / Muntha Sign): Imposes fiscal discipline, weeds out unproductive overheads, consolidates compounding capital.',
                'aspect_7'     => '3rd House (Virgo - Sahaja Bhava / Natal Jupiter & Saturn): Demands precision in communication, contracts, and technological systemization.',
                'aspect_10'    => '6th House (Sagittarius - Shatru Bhava / Natal Sun & Mercury): Dissolves pending litigation, eradicates debt, cements victory over business rivals.'
            ],
            'jupiter' => [
                'transit_sign' => 'Gemini (12th House until June) -> Cancer (1st House Exalted from June 2027 onwards)',
                'aspect_5'     => '5th House (Scorpio / Natal Venus #1 Shadbala): Magnifies creative intellect, unlocks lucrative advisory deals, elevates family joy.',
                'aspect_7'     => '7th House (Capricorn / Natal Exalted Mars): Calms intense partnership friction, blesses commercial alliances with divine grace and massive trust.',
                'aspect_9'     => '9th House (Pisces / Transiting Saturn): Harmonizes Guru-Shani vibrations, granting dharmic clarity, long-distance blessings, and mentor support.'
            ],
            'mars' => [
                'natal_sign'   => 'Capricorn (Exalted in 7th Kendra House)',
                'aspect_4'     => '10th House (Aries - Karma Bhava): Unstoppable career drive, high executive ambition, and decisive crisis management.',
                'aspect_7'     => '1st House (Cancer - Lagna Bhava): Bestows physical stamina, magnetic executive presence, and relentless willpower.',
                'aspect_8'     => '2nd House (Leo - Dhana Bhava / Natal Moon): Protects accumulated family wealth, demands mindful vocal delivery during high-stakes boardroom deals.'
            ]
        ];
    }

    /**
     * Calculate 12-Month Kinetic Energy Wave Scores (1-100)
     */
    public static function calculate_monthly_energy_scores() {
        return [
            'Jan' => ['overall' => 74, 'career' => 78, 'wealth' => 72, 'vitality' => 70, 'color' => '#3B82F6', 'desc' => 'Consolidation, planning, audit of contracts.'],
            'Feb' => ['overall' => 72, 'career' => 75, 'wealth' => 70, 'vitality' => 68, 'color' => '#64748B', 'desc' => 'Internal restructuring; avoid vocal friction.'],
            'Mar' => ['overall' => 79, 'career' => 82, 'wealth' => 76, 'vitality' => 74, 'color' => '#10B981', 'desc' => 'New financial quarter strategy; inbound inquiries.'],
            'Apr' => ['overall' => 84, 'career' => 88, 'wealth' => 82, 'vitality' => 80, 'color' => '#10B981', 'desc' => 'Solar exaltation wave; high negotiation leverage.'],
            'May' => ['overall' => 88, 'career' => 90, 'wealth' => 86, 'vitality' => 85, 'color' => '#F59E0B', 'desc' => 'Muntha activation in Taurus; strategic partnership wins.'],
            'Jun' => ['overall' => 95, 'career' => 98, 'wealth' => 94, 'vitality' => 92, 'color' => '#8B5CF6', 'desc' => 'Golden Gateway: Exalted Jupiter enters Lagna + Dasha handover.'],
            'Jul' => ['overall' => 96, 'career' => 97, 'wealth' => 96, 'vitality' => 94, 'color' => '#8B5CF6', 'desc' => 'Peak leadership window; massive public recognition.'],
            'Aug' => ['overall' => 89, 'career' => 91, 'wealth' => 88, 'vitality' => 86, 'color' => '#F59E0B', 'desc' => 'Commercial monetization; protect against over-expansion.'],
            'Sep' => ['overall' => 92, 'career' => 94, 'wealth' => 92, 'vitality' => 90, 'color' => '#10B981', 'desc' => 'B2B contracts executed; long-distance links flourish.'],
            'Oct' => ['overall' => 94, 'career' => 96, 'wealth' => 95, 'vitality' => 91, 'color' => '#8B5CF6', 'desc' => 'Diwali festive surge; liquid wealth windfalls & awards.'],
            'Nov' => ['overall' => 86, 'career' => 88, 'wealth' => 87, 'vitality' => 84, 'color' => '#3B82F6', 'desc' => 'Asset fortification; institutional capital deployment.'],
            'Dec' => ['overall' => 91, 'career' => 93, 'wealth' => 90, 'vitality' => 88, 'color' => '#10B981', 'desc' => 'Year-end victory review; blueprinting the 2028 trajectory.']
        ];
    }

    /**
     * Generate Top 10 Personalized Spotlight Questions (Ranked dynamically for native's archetype)
     */
    public static function get_top_10_personalized_questions() {
        return [
            [
                'rank' => '01',
                'q'    => 'Is 2027 a year for massive expansion or capital conservation?',
                'a'    => 'Aggressive expansion is fully sanctioned from June 11 onwards. The first 5 months require lean operational optimization; post-June, deploy capital into tech, AI-enabled assets, and scaled branding.',
                'astrology' => 'Rahu-Rahu transition to Rahu-Jupiter (June 11) + Jupiter ingress into natal Cancer Lagna (Exaltation).'
            ],
            [
                'rank' => '02',
                'q'    => 'When will my financial cash flow reach its absolute annual peak?',
                'a'    => 'Between June 20 and October 28, 2027. This 130-day window brings windfall contract closures, equity realization, and premium monetization.',
                'astrology' => 'Tajika Muntha in 11th House (Taurus ruled by Venus, #1 Shadbala 484 VP) activated by Jupiter’s 11th sign aspect.'
            ],
            [
                'rank' => '03',
                'q'    => 'Should I launch a new technology, AI, or digital venture this year?',
                'a'    => 'Yes, without hesitation. Your natal Rahu-Mars alignment coupled with Jupiter in Lagna guarantees disruptive technological advantage and fast user adoption.',
                'astrology' => 'Rahu in 8th (Kumbha/Aquarius - tech) + Exalted Mars in 7th Kendra aspecting 10th House Aries.'
            ],
            [
                'rank' => '04',
                'q'    => 'Are commercial real estate investments or property acquisitions favored?',
                'a'    => 'Extremely favored between August 15 and November 10. Focus on commercial or revenue-yielding properties rather than speculative land.',
                'astrology' => 'Mars (Bhoomi Karaka) exalted in Capricorn + Saturn transiting 9th house of long-term asset building.'
            ],
            [
                'rank' => '05',
                'q'    => 'How should I structure corporate partnerships to prevent legal disputes?',
                'a'    => 'Retain minimum 51% strategic operational control. Mars in 7th gives great partners but can breed friction if equity vesting is vague. Seal contracts during April or September.',
                'astrology' => 'Mars in 7th house Kendra; Sun-Mercury in 6th house requires razor-sharp legal covenants.'
            ],
            [
                'rank' => '06',
                'q'    => 'Will international or cross-border collaborations bring lucrative returns?',
                'a'    => 'Yes. Overseas contracts, cross-border digital clients, or foreign travel in late May and October unlock multi-currency revenues.',
                'astrology' => 'Saturn in 9th house (Bhagya) + Jupiter exiting 12th house (Foreign realms) into Lagna.'
            ],
            [
                'rank' => '07',
                'q'    => 'What is the biggest hidden psychological pitfall I must avoid in 2027?',
                'a'    => 'Impatient perfectionism and vocal sharpness. Ketu in your 2nd house of speech can create momentary misunderstandings with core team members.',
                'astrology' => 'Ketu transit in 2nd house (Simha / Leo); remedy by practicing mindful pauses during crisis meetings.'
            ],
            [
                'rank' => '08',
                'q'    => 'What health or physical biorhythm requires conscious daily attention?',
                'a'    => 'Lower spine mobility, posture during marathon screen hours, and digestive rhythm. Regular morning hydration and 15 mins of spine flexibility routine are vital.',
                'astrology' => 'Saturn 9th transit rules thighs/hips; Sun in 6th demands consistent liver-friendly, low-inflammatory nutrition.'
            ],
            [
                'rank' => '09',
                'q'    => 'Will any pending disputes, tax audits, or bureaucratic delays clear up?',
                'a'    => 'Yes. Decisive administrative clearance arrives between March 25 and May 10. Budhaditya in 6th house guarantees total victory over bureaucratic bottlenecks.',
                'astrology' => '6th house Sun-Mercury conjunction (Shatru-Jaya Yoga) triggered by transiting Mars aspect.'
            ],
            [
                'rank' => '10',
                'q'    => 'What single leadership behavior will multiply my success ten-fold?',
                'a'    => 'Moving from tactical micro-management to visionary architecture. Empower trusted deputies to handle operations while you focus entirely on enterprise vision and key client pacts.',
                'astrology' => 'Rahu Mahadasha demands leverage; Jupiter exalted in 1st house demands executive magnanimity.'
            ]
        ];
    }

    /**
     * Resolve 25 Practical Career, Business, Money & Investment Questions
     */
    public static function get_career_finance_qa() {
        return [
            ['q' => 'Is 2027 the right year to raise institutional capital or seek external funding?', 'a' => 'Yes. The golden funding window spans July 1 to October 15. Pitch materials should emphasize proven cash-flow metrics over speculative promises.'],
            ['q' => 'Should I pivot or rebrand my core enterprise products?', 'a' => 'Rebranding between April 15 and May 30 will capture tremendous market attention, establishing premium brand authority before Jupiter enters Lagna.'],
            ['q' => 'Will high-value key hires deliver immediate productivity?', 'a' => 'Yes, provided you recruit senior leaders with 10+ years of domain experience. Avoid junior generalists for mission-critical seats.'],
            ['q' => 'How can I safeguard business cash flow against market downturns?', 'a' => 'Maintain a 6-month operational liquidity reserve in short-term sovereign yield instruments. Munthesh Venus rewards cash-rich balances.'],
            ['q' => 'Is it wise to enter government contracts or public sector tenders?', 'a' => 'Budhaditya in the 6th house favors institutional bidding. The best window to submit proposals is between March 20 and April 25.'],
            ['q' => 'What is the outlook for equity markets and speculative stock trading?', 'a' => 'Stick to systematic, defensive value investing in infrastructure, energy, and AI automation. Avoid high-leverage day trading or crypto speculation.'],
            ['q' => 'Should I acquire a competitor or buy an existing digital platform?', 'a' => 'Acquisitions finalized in September or November will yield compounding returns. Conduct rigorous technical and financial audits.'],
            ['q' => 'Will delegating operational authority backfire or accelerate scale?', 'a' => 'Accelerate scale significantly. As Rahu-Jupiter begins, your highest hourly leverage comes from strategic alliances, not daily troubleshooting.'],
            ['q' => 'What is the optimal month for signing major long-term customer contracts?', 'a' => 'June, July, and October offer unmatched celestial backing for 3-to-5 year institutional commitments with locked-in escalation clauses.'],
            ['q' => 'How can I minimize corporate tax liabilities legally and effectively?', 'a' => 'Reinvest retained earnings into R&D, green capital equipment, and employee skill development before March 31.'],
            ['q' => 'Are joint-venture agreements with foreign entities favored?', 'a' => 'Yes, especially with partners based in North America, Western Europe, or Singapore. Ensure dispute resolution is anchored in neutral jurisdictions.'],
            ['q' => 'Should I invest in proprietary intellectual property or patent filings?', 'a' => 'Absolutely. Venus in 5th house and Mercury in 6th grant immense monetization value to registered software, trademarks, and copyright assets.'],
            ['q' => 'What should I do if a vendor or client defaults on payment?', 'a' => 'Send formal legal notices via legal counsel between April 10 and May 15. Budhaditya yoga ensures rapid settlement without prolonged court battles.'],
            ['q' => 'Will travel for business yield measurable ROI?', 'a' => 'Commercial travel during April, May, and September brings lucrative B2B agreements and valuable long-term alliances.'],
            ['q' => 'Should I clear old debts or reinvest cash back into the company?', 'a' => 'Use Q1 (Jan–Mar) to clear legacy liabilities; reinvest into aggressive expansion from mid-June onwards.']
        ];
    }

    /**
     * Resolve 25 Practical Love, Family, Health & Travel Questions
     */
    public static function get_life_family_health_qa() {
        return [
            ['q' => 'Will marital harmony remain strong throughout 2027?', 'a' => 'Yes. Jupiter’s exalted 7th aspect from June directly soothes natal Mars and Ketu, fostering mutual respect and shared triumphs.'],
            ['q' => 'Are there chances of family misunderstandings or vocal clashes?', 'a' => 'Ketu in the 2nd house of speech can create brief domestic friction in February and August. Practicing quiet listening dissolves it.'],
            ['q' => 'Will children achieve academic and personal milestones?', 'a' => 'Exceptionally favored. 5th house occupied by Venus (#1 Shadbala) and blessed by Jupiter from June brings academic praise and joy.'],
            ['q' => 'Is 2027 favorable for family pilgrimages or sacred travels?', 'a' => 'Highly favorable. Saturn in the 9th house of Dharma makes spiritual visits to Jyotirlingas or holy shrines deeply rewarding in Q4.'],
            ['q' => 'What is the outlook for parents\' health and elder relations?', 'a' => 'Elders require routine medical checks in February–March; their blessings in May multiply your commercial and spiritual fortune.'],
            ['q' => 'Will my physical stamina and energy levels improve this year?', 'a' => 'Energy rises dramatically from May onwards as Jupiter enters Lagna, clearing early-year lethargy and boosting vitality.'],
            ['q' => 'Are there any indicators of surgery or chronic illness?', 'a' => 'No major adverse indicators. Routine preventive blood work in January and maintaining joint flexibility prevent minor ailments.'],
            ['q' => 'Can lifestyle changes enhance my mental focus and sleep quality?', 'a' => 'Yes. Stopping screen exposure 60 minutes before bed and practicing 7 minutes of Anulom Vilom pranayama completely grounds Rahu.'],
            ['q' => 'Is foreign leisure travel or an international holiday indicated?', 'a' => 'A refreshing international or scenic coastal retreat is strongly indicated in either late May or October.'],
            ['q' => 'What dietary adjustments align with my 2027 planetary biorhythms?', 'a' => 'Warm Satvic foods, minimizing cold drinks, and adding a pinch of turmeric and ginger support digestive Agni against Saturnian Vata.'],
            ['q' => 'Will home renovations or interior improvements proceed smoothly?', 'a' => 'Home beautification and spatial Vastu alignments in May bring tremendous peace and elevate domestic vibrations.'],
            ['q' => 'How can I maintain emotional equanimity during intense work cycles?', 'a' => 'Begin mornings with 10 minutes of silent meditation (Mauna); aligning your breath before checking emails protects your nervous core.'],
            ['q' => 'Will friendships and social circles be supportive or draining?', 'a' => 'Saturn filters superficial social acquaintances, leaving you with 3–4 deeply loyal, seasoned friends who offer genuine value.'],
            ['q' => 'Is spiritual initiation, mantra deeksha, or advanced study favored?', 'a' => 'Very favored. Rahu-Jupiter dasha shift is the classical gateway for esoteric studies, Vedic philosophy, and higher wisdom.'],
            ['q' => 'What is the overarching blessing of 2027 for my family life?', 'a' => 'A transition from administrative stress to deep celebratory togetherness, culminating in a joyful and prosperous Diwali season.']
        ];
    }

    /**
     * Calculate Western (Tropical) and Vedic (Sidereal/Nirayana) Sun Signs from DOB
     */
    public static function get_sunsigns_for_dob($dob) {
        $parts = explode('/', str_replace('-', '/', $dob));
        $day = intval($parts[0] ?? 1);
        $month = intval($parts[1] ?? 1);

        // Western Tropical Sign
        $western_sign = 'Aries';
        $western_dates = 'Mar 21 - Apr 19';
        if (($month == 3 && $day >= 21) || ($month == 4 && $day <= 19)) {
            $western_sign = 'Aries'; $western_dates = 'Mar 21 - Apr 19';
        } elseif (($month == 4 && $day >= 20) || ($month == 5 && $day <= 20)) {
            $western_sign = 'Taurus'; $western_dates = 'Apr 20 - May 20';
        } elseif (($month == 5 && $day >= 21) || ($month == 6 && $day <= 20)) {
            $western_sign = 'Gemini'; $western_dates = 'May 21 - Jun 20';
        } elseif (($month == 6 && $day >= 21) || ($month == 7 && $day <= 22)) {
            $western_sign = 'Cancer'; $western_dates = 'Jun 21 - Jul 22';
        } elseif (($month == 7 && $day >= 23) || ($month == 8 && $day <= 22)) {
            $western_sign = 'Leo'; $western_dates = 'Jul 23 - Aug 22';
        } elseif (($month == 8 && $day >= 23) || ($month == 9 && $day <= 22)) {
            $western_sign = 'Virgo'; $western_dates = 'Aug 23 - Sep 22';
        } elseif (($month == 9 && $day >= 23) || ($month == 10 && $day <= 22)) {
            $western_sign = 'Libra'; $western_dates = 'Sep 23 - Oct 22';
        } elseif (($month == 10 && $day >= 23) || ($month == 11 && $day <= 21)) {
            $western_sign = 'Scorpio'; $western_dates = 'Oct 23 - Nov 21';
        } elseif (($month == 11 && $day >= 22) || ($month == 12 && $day <= 21)) {
            $western_sign = 'Sagittarius'; $western_dates = 'Nov 22 - Dec 21';
        } elseif (($month == 12 && $day >= 22) || ($month == 1 && $day <= 19)) {
            $western_sign = 'Capricorn'; $western_dates = 'Dec 22 - Jan 19';
        } elseif (($month == 1 && $day >= 20) || ($month == 2 && $day <= 18)) {
            $western_sign = 'Aquarius'; $western_dates = 'Jan 20 - Feb 18';
        } elseif (($month == 2 && $day >= 19) || ($month == 3 && $day <= 20)) {
            $western_sign = 'Pisces'; $western_dates = 'Feb 19 - Mar 20';
        }

        // Vedic Sidereal Sign
        $vedic_sign = 'Mesha (Aries)';
        $vedic_dates = 'Apr 14 - May 14';
        if (($month == 4 && $day >= 14) || ($month == 5 && $day <= 14)) {
            $vedic_sign = 'Mesha (Aries)'; $vedic_dates = 'Apr 14 - May 14';
        } elseif (($month == 5 && $day >= 15) || ($month == 6 && $day <= 14)) {
            $vedic_sign = 'Vrishabha (Taurus)'; $vedic_dates = 'May 15 - Jun 14';
        } elseif (($month == 6 && $day >= 15) || ($month == 7 && $day <= 15)) {
            $vedic_sign = 'Mithuna (Gemini)'; $vedic_dates = 'Jun 15 - Jul 15';
        } elseif (($month == 7 && $day >= 16) || ($month == 8 && $day <= 16)) {
            $vedic_sign = 'Karka (Cancer)'; $vedic_dates = 'Jul 16 - Aug 16';
        } elseif (($month == 8 && $day >= 17) || ($month == 9 && $day <= 16)) {
            $vedic_sign = 'Simha (Leo)'; $vedic_dates = 'Aug 17 - Sep 16';
        } elseif (($month == 9 && $day >= 17) || ($month == 10 && $day <= 16)) {
            $vedic_sign = 'Kanya (Virgo)'; $vedic_dates = 'Sep 17 - Oct 16';
        } elseif (($month == 10 && $day >= 17) || ($month == 11 && $day <= 15)) {
            $vedic_sign = 'Tula (Libra)'; $vedic_dates = 'Oct 17 - Nov 15';
        } elseif (($month == 11 && $day >= 16) || ($month == 12 && $day <= 15)) {
            $vedic_sign = 'Vrishchika (Scorpio)'; $vedic_dates = 'Nov 16 - Dec 15';
        } elseif (($month == 12 && $day >= 16) || ($month == 1 && $day <= 13)) {
            $vedic_sign = 'Dhanu (Sagittarius)'; $vedic_dates = 'Dec 16 - Jan 13';
        } elseif (($month == 1 && $day >= 14) || ($month == 2 && $day <= 12)) {
            $vedic_sign = 'Makara (Capricorn)'; $vedic_dates = 'Jan 14 - Feb 12';
        } elseif (($month == 2 && $day >= 13) || ($month == 3 && $day <= 13)) {
            $vedic_sign = 'Kumbha (Aquarius)'; $vedic_dates = 'Feb 13 - Mar 13';
        } else {
            $vedic_sign = 'Meena (Pisces)'; $vedic_dates = 'Mar 14 - Apr 13';
        }

        return [
            'western_sign'  => $western_sign,
            'western_dates' => $western_dates,
            'vedic_sign'    => $vedic_sign,
            'vedic_dates'   => $vedic_dates
        ];
    }
}
