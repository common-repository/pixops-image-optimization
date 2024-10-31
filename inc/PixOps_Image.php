<?php
include plugin_dir_path(__FILE__) . 'PixOps_Content.php';
include plugin_dir_path(__FILE__) . 'PixOps_Feature.php';
class PixOps_Image
{
    public $image_sizes = array();
    public $custom_size_buffer = array();

    public static function pixops_content($content)
    {
        $html = PixOps_Content::pixops_images($content);
        return PixOps_Image::pixops_parse_url($html);
    }

    public static function pixops_parse_url($html)
    {

        $regex = '/(?:[(|\s\';",=])((?:http|\/|\\\\){1}(?:[\/:,~\\\\.\-\–\d_@%A-Za-z-ÁÀȦÂÄǞǍĂĀÃÅǺǼǢĆĊĈČĎḌḐḒÉÈĖÊËĚĔĒẼE̊ẸǴĠĜǦĞG̃ĢĤḤáàȧâäǟǎăāãåǻǽǣćċĉčďḍḑḓéèėêëěĕēẽe̊ẹǵġĝǧğg̃ģĥḥÍÌİÎÏǏĬĪĨỊĴĶǨĹĻĽĿḼM̂M̄ʼNŃN̂ṄN̈ŇN̄ÑŅṊÓÒȮȰÔÖȪǑŎŌÕȬŐỌǾƠíìiîïǐĭīĩịĵķǩĺļľŀḽm̂m̄ŉńn̂ṅn̈ňn̄ñņṋóòôȯȱöȫǒŏōõȭőọǿơP̄ŔŘŖŚŜṠŠȘṢŤȚṬṰÚÙÛÜǓŬŪŨŰŮỤẂẀŴẄÝỲŶŸȲỸŹŻŽẒǮp̄ŕřŗśŝṡšşṣťțṭṱúùûüǔŭūũűůụẃẁŵẅýỳŷÿȳỹźżžẓǯßœŒçÇ®]{10,}\.(?:' . implode('|', array(
         'jpg','jpeg','jpe',
            'png',
            'webp',
            'svg')) . ')))(?=(?:|\?|"|&|,|\s|\'|\)|\||\\\\|}))/U';
        preg_match_all(
            $regex,
            $html,
            $urls
        );
        $urls_unique = array_map(
            function ($value) {
                $value = str_replace('&quot;', '', $value);

                return rtrim($value, '\\";\'');
            },
            $urls[1]
        );
        $urls_unique = array_unique($urls_unique);

        $values = array_values($urls_unique);
        foreach ($values as $item) {
            if (strpos($item, "cdn.pixops.io") > 0) {
                continue;
            }

            $html = str_replace($item, PixOps_Feature::pixops_returnImageUrl($item, '')['pixopsSrc'], $html);
        }
        return $html;
		

    }
    public static function pixops_post_thumbnail($url)
    {
        return PixOps_Feature::pixops_process_image_feature($url);
    }

    public static function pixops_post_attachment_tag($url, $srctag)
    {
        return PixOps_Feature::pixops_returnImageUrl($url, $srctag);
    }
}
