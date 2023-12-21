<?php
/**
 * SpamLite评论过滤器，SmartSpam简化版
 * 
 * @package SpamLite
 * @author YoviSun 陶小桃Blog
 * @version 0.0.2
 * @link http://www.52txr.cn
 */

class SpamLite_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('Widget_Feedback')->comment = array('SpamLite_Plugin', 'filter');
        return _t('SpamLite插件启用成功，请配置需要过滤的内容');
    }

    public static function deactivate(){}

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        $opt_sensitive_words = new Typecho_Widget_Helper_Form_Element_Radio('opt_sensitive_words',
            array("none" => "无动作", "waiting" => "标记为待审核", "abandon" => "评论失败"), "none",
            _t('敏感词汇操作'), "如果评论中包含敏感词汇列表中的词汇，将执行该操作");
        $form->addInput($opt_sensitive_words);

        $words_sensitive = new Typecho_Widget_Helper_Form_Element_Textarea('words_sensitive', NULL, "",
            _t('敏感词汇'), _t('多条词汇请用换行符隔开'));
        $form->addInput($words_sensitive);

        $opt_no_chinese = new Typecho_Widget_Helper_Form_Element_Radio('opt_no_chinese',
            array("none" => "无动作", "waiting" => "标记为待审核", "abandon" => "评论失败"), "none",
            _t('非中文评论操作'), "如果评论中不包含中文，则执行该操作");
        $form->addInput($opt_no_chinese);

        $opt_sensitive_nickname = new Typecho_Widget_Helper_Form_Element_Radio('opt_sensitive_nickname',
            array("none" => "无动作", "waiting" => "标记为待审核", "abandon" => "评论失败"), "none",
            _t('敏感昵称操作'), "如果评论者的昵称包含敏感词汇列表中的词汇，将执行该操作");
        $form->addInput($opt_sensitive_nickname);

        $words_sensitive_nickname = new Typecho_Widget_Helper_Form_Element_Textarea('words_sensitive_nickname', NULL, "",
            _t('敏感昵称词汇'), _t('多条词汇请用换行符隔开'));
        $form->addInput($words_sensitive_nickname);

        $opt_sensitive_url = new Typecho_Widget_Helper_Form_Element_Radio('opt_sensitive_url',
            array("none" => "无动作", "waiting" => "标记为待审核", "abandon" => "评论失败"), "none",
            _t('敏感网址操作'), "如果评论者的网址包含敏感词汇列表中的词汇，将执行该操作");
        $form->addInput($opt_sensitive_url);

        $words_sensitive_url = new Typecho_Widget_Helper_Form_Element_Textarea('words_sensitive_url', NULL, "",
            _t('敏感网址词汇'), _t('多条词汇请用换行符隔开'));
        $form->addInput($words_sensitive_url);

        $opt_sensitive_email = new Typecho_Widget_Helper_Form_Element_Radio('opt_sensitive_email',
            array("none" => "无动作", "waiting" => "标记为待审核", "abandon" => "评论失败"), "none",
            _t('敏感邮箱操作'), "如果评论者的邮箱包含敏感词汇列表中的词汇，将执行该操作");
        $form->addInput($opt_sensitive_email);

        $words_sensitive_email = new Typecho_Widget_Helper_Form_Element_Textarea('words_sensitive_email', NULL, "",
            _t('敏感邮箱词汇'), _t('多条词汇请用换行符隔开'));
        $form->addInput($words_sensitive_email);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form){}

    public static function filter($comments, $post, $last)
    {
        $comment = empty($last) ? $comments : $last;
        $options = Typecho_Widget::widget('Widget_Options');
        $user = Typecho_Widget::widget('Widget_User');
        $filter_set = $options->plugin('SpamLite');
        $opt = "none";
        $error = "";

        // 检查敏感词汇
        if ($opt == "none" && $filter_set->opt_sensitive_words != "none") {
            if (SpamLite_Plugin::check_in($filter_set->words_sensitive, $comment['text'])) {
                $error = "评论内容中包含敏感词汇";
                $opt = $filter_set->opt_sensitive_words;
            }
        }

        // 非中文评论处理
        if ($opt == "none" && $filter_set->opt_no_chinese != "none") {
            if (preg_match("/[\x{4e00}-\x{9fa5}]/u", $comment['text']) == 0) {
                $error = "评论内容请包含至少一个中文汉字";
                $opt = $filter_set->opt_no_chinese;
            }
        }

        // 检查敏感昵称
        if ($opt == "none" && $filter_set->opt_sensitive_nickname != "none") {
            if (SpamLite_Plugin::check_in($filter_set->words_sensitive_nickname, $comment['author'])) {
                $error = "评论者的昵称包含敏感词汇";
                $opt = $filter_set->opt_sensitive_nickname;
            }
        }
        
        // 检查敏感网址
        if ($opt == "none" && $filter_set->opt_sensitive_url != "none" && !empty($comment['url'])) {
            if (SpamLite_Plugin::check_in($filter_set->words_sensitive_url, $comment['url'])) {
                $error = "评论者的网址包含敏感词汇";
                $opt = $filter_set->opt_sensitive_url;
            }
        }

        // 检查敏感邮箱
        if ($opt == "none" && $filter_set->opt_sensitive_email != "none" && !empty($comment['mail'])) {
            if (SpamLite_Plugin::check_in($filter_set->words_sensitive_email, $comment['mail'])) {
                $error = "评论者的邮箱包含敏感词汇";
                $opt = $filter_set->opt_sensitive_email;
            }
        }

        // 根据处理结果执行相应操作
        switch ($opt) {
            case "waiting":
                $comment['status'] = 'waiting';
                $comments = $comment;
                break;
            case "abandon":
                $error = empty($error) ? "评论失败" : $error;
                throw new Typecho_Widget_Exception(_t($error));
                break;
            default:
                break;
        }

        return $comments;
    }

    // 检查字符串中是否包含关键词
    public static function check_in($needles, $haystack)
    {
        $needles = explode("\n", $needles);
        foreach ($needles as $needle) {
            $needle = trim($needle);
            if ($needle != '' && strpos($haystack, $needle) !== false) {
                return true;
            }
        }
        return false;
    }
}        
        
        
        