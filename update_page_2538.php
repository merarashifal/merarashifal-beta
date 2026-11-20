<?php
require_once __DIR__ . '/wp-load.php';
global $wpdb;

$content = <<<HTML
<div class="muhurat-dates-main" style="max-width:1040px; margin:0 auto; padding:12px; font-family:-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height:1.7; color:#2d2420;">

  <nav aria-label="Breadcrumb" style="font-size:13px; margin-bottom:18px; color:#78665b;">
    <a href="/" style="color:#8c2514; text-decoration:none;">Home</a> &raquo; 
    <span>Shubh Muhurat Dates</span>
  </nav>

  <h1 style="color:#8c2514; font-size:28px; line-height:1.3; font-weight:700; margin-bottom:12px;">
    शुभ मुहूर्त तिथियां | Shubh Muhurat Dates 2026-2027
  </h1>

  <p style="font-size:15px; color:#4a3b32; margin-bottom:20px;">
    वैदिक ज्योतिष अनुसार किसी भी महत्वपूर्ण कार्य, मांगलिक संस्कार अथवा नवीन निवेश का शुभारंभ शुभ मुहूर्त में करने से सफलता, दीर्घायु एवं समृद्धि की प्राप्ति होती है। नीचे वर्ष 2026 एवं 2027 के समस्त शुभ मुहूर्त (विवाह, गृह प्रवेश, वाहन एवं संपत्ति खरीद) की प्रामाणिक तिथियां, नक्षत्र, तिथि एवं शुभ समय उपलब्ध हैं।
  </p>

  <!-- 2027 HIGHLIGHT CARDS -->
  <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(260px, 1fr)); gap:16px; margin:24px 0 28px;">
    <div style="background:#fff8f0; border:2px solid #e7cdb8; border-radius:12px; padding:18px; text-align:center;">
      <div style="font-size:28px; margin-bottom:8px;">💍</div>
      <h3 style="font-size:17px; margin:0 0 6px; color:#8c2514; font-weight:700;">विवाह मुहूर्त 2027</h3>
      <p style="font-size:13px; color:#6b584e; margin:0 0 12px;">वर्ष 2027 के सभी शुभ सावे, विवाह लग्न एवं तिथियां।</p>
      <a href="/vivah-muhurat-2027/" style="display:inline-block; background:#8c2514; color:#fff !important; padding:7px 16px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;">विवाह मुहूर्त 2027 देखें &rarr;</a>
    </div>

    <div style="background:#fff8f0; border:2px solid #e7cdb8; border-radius:12px; padding:18px; text-align:center;">
      <div style="font-size:28px; margin-bottom:8px;">🏡</div>
      <h3 style="font-size:17px; margin:0 0 6px; color:#8c2514; font-weight:700;">गृह प्रवेश मुहूर्त 2027</h3>
      <p style="font-size:13px; color:#6b584e; margin:0 0 12px;">नए घर, फ्लैट व विला में प्रवेश के समस्त शुभ नक्षत्र व समय।</p>
      <a href="/griha-pravesh-muhurat-2027/" style="display:inline-block; background:#8c2514; color:#fff !important; padding:7px 16px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;">गृह प्रवेश 2027 देखें &rarr;</a>
    </div>

    <div style="background:#fff8f0; border:2px solid #e7cdb8; border-radius:12px; padding:18px; text-align:center;">
      <div style="font-size:28px; margin-bottom:8px;">🚗</div>
      <h3 style="font-size:17px; margin:0 0 6px; color:#8c2514; font-weight:700;">वाहन एवं संपत्ति 2027</h3>
      <p style="font-size:13px; color:#6b584e; margin:0 0 12px;">कार, बाइक, भूमि व फ्लैट खरीद के श्रेष्ठ योग व तिथियां।</p>
      <a href="/vehicle-property-purchase-muhurat-2027/" style="display:inline-block; background:#8c2514; color:#fff !important; padding:7px 16px; border-radius:6px; font-size:13px; font-weight:600; text-decoration:none;">वाहन एवं संपत्ति 2027 &rarr;</a>
    </div>
  </div>

  <!-- DYNAMIC MUHURAT VIEWER -->
  <div style="margin:20px 0;">
    [muhurat_viewer]
  </div>

  <!-- QUICK NAVIGATION PILLS -->
  <div style="display:flex; flex-wrap:wrap; gap:12px; margin:25px 0 30px; justify-content:center;">
    <a href="/panchang/" style="background:#FAF6F0; border:1px solid #ebd9c8; color:#8c2514; padding:8px 18px; border-radius:20px; font-weight:600; font-size:14px; text-decoration:none;">📅 आज का पंचांग (Today's Panchang)</a>
    <a href="/horoscope/" style="background:#FAF6F0; border:1px solid #ebd9c8; color:#8c2514; padding:8px 18px; border-radius:20px; font-weight:600; font-size:14px; text-decoration:none;">♈ आज का राशिफल (Daily Horoscope)</a>
    <a href="/reports/kundali-and-gun-matching-report/" style="background:#FAF6F0; border:1px solid #ebd9c8; color:#8c2514; padding:8px 18px; border-radius:20px; font-weight:600; font-size:14px; text-decoration:none;">💍 कुंडली मिलान रिपोर्ट (Gun Milan)</a>
  </div>

  <!-- IN-DEPTH GUIDE SECTIONS -->
  <div style="margin-top:35px; border-top:1px solid #ebd9c8; padding-top:25px;">
    <h2 style="color:#8c2514; font-size:22px; font-weight:700; margin-bottom:10px;">
      1. विवाह मुहूर्त (Vivah Muhurat)
    </h2>
    <p style="font-size:15px; color:#4a3b32; line-height:1.7;">
      विवाह संस्कार में शुभ मुहूर्त का विचार वर और वधू दोनों के सुखद दांपत्य जीवन का आधार बनता है। इसमें गुरु बल, सूर्य बल और चंद्र शुद्धि के साथ-साथ रोहिणी, मृगशिरा, मघा, अनुराधा, रेवती आदि शुभ नक्षत्रों का विशेष महत्व है।
      <br><a href="/vivah-muhurat-2027/" style="color:#8c2514; font-weight:600;">विवाह मुहूर्त 2027 की संपूर्ण सूची एवं विवरण के लिए यहां क्लिक करें &rarr;</a>
    </p>

    <h2 style="color:#8c2514; font-size:22px; font-weight:700; margin:25px 0 10px;">
      2. गृह प्रवेश मुहूर्त (Griha Pravesh Muhurat)
    </h2>
    <p style="font-size:15px; color:#4a3b32; line-height:1.7;">
      नए घर में प्रवेश करते समय वास्तु पुरुष और नवग्रहों की कृपा प्राप्त करने के लिए शुभ तिथि एवं लग्न का चयन किया जाता है। माघ, फाल्गुन, वैशाख और ज्येष्ठ मास गृह प्रवेश के लिए सर्वश्रेष्ठ फल प्रदान करते हैं।
      <br><a href="/griha-pravesh-muhurat-2027/" style="color:#8c2514; font-weight:600;">गृह प्रवेश मुहूर्त 2027 की संपूर्ण सूची एवं पूजा विधि के लिए यहां क्लिक करें &rarr;</a>
    </p>

    <h2 style="color:#8c2514; font-size:22px; font-weight:700; margin:25px 0 10px;">
      3. संपत्ति एवं भूमि खरीद मुहूर्त (Property Purchase Muhurat)
    </h2>
    <p style="font-size:15px; color:#4a3b32; line-height:1.7;">
      भूमि, मकान या व्यावसायिक प्रतिष्ठान का क्रय, एग्रीमेंट और रजिस्ट्री शुभ योगों में करने से निवेश सुरक्षित रहता है और संपत्ति विवादों से मुक्त रहती है।
      <br><a href="/property-purchase-muhurat-2027/" style="color:#8c2514; font-weight:600;">संपत्ति खरीद मुहूर्त 2027 के शुभ दिनों की सूची देखें &rarr;</a>
    </p>

    <h2 style="color:#8c2514; font-size:22px; font-weight:700; margin:25px 0 10px;">
      4. वाहन खरीद मुहूर्त (Vehicle Purchase Muhurat)
    </h2>
    <p style="font-size:15px; color:#4a3b32; line-height:1.7;">
      कार, बाइक अथवा व्यावसायिक वाहन की खरीद व डिलीवरी के समय शुक्र देव के आशीर्वाद व शुभ चौघड़िया का ध्यान रखना चाहिए ताकि प्रत्येक यात्रा सुखद और सुरक्षित बनी रहे।
      <br><a href="/vehicle-purchase-muhurat-2027/" style="color:#8c2514; font-weight:600;">वाहन खरीद मुहूर्त 2027 की संपूर्ण तिथियां देखें &rarr;</a>
    </p>
  </div>

</div>
HTML;

wp_update_post([
    'ID'           => 2538,
    'post_content' => $content,
]);

// Update SEO meta
update_post_meta(2538, '_yoast_wpseo_title', 'Shubh Muhurat Dates (शुभ मुहूर्त तिथियां 2026-2027) %%sep%% %%sitename%%');
update_post_meta(2538, '_yoast_wpseo_metadesc', 'वर्ष 2026-2027 में विवाह मुहूर्त, गृह प्रवेश, वाहन खरीद एवं संपत्ति खरीद के समस्त प्रामाणिक शुभ मुहूर्त, तिथियां, नक्षत्र एवं शुभ समय।');
update_post_meta(2538, 'ast-title-bar-display', 'disabled'); // Prevent Astra duplicate H1

echo "Page 2538 successfully updated with clean H1, 2027 hubs, and relative URLs!\n";
