<?php
/**
 * SpamLite评论过滤器，SmartSpam简化版
 * 
 * @package SpamLite
 * @author YoviSun 陶小桃Blog
 * @version 0.0.1
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
            array("none" => "无动作", "waiting" => "待审核", "abandon" => "评论失败"), "none",
            _t('敏感词汇操作'), "如果评论中包含敏感词汇列表中的词汇，将执行该操作");
        $form->addInput($opt_sensitive_words);

        $words_sensitive = new Typecho_Widget_Helper_Form_Element_Textarea('words_sensitive', NULL, "",
            _t('敏感词汇'), _t('多条词汇请用换行符隔开'));
        $form->addInput($words_sensitive);

        $opt_no_chinese = new Typecho_Widget_Helper_Form_Element_Radio('opt_no_chinese',
            array("none" => "无动作", "waiting" => "待审核", "abandon" => "评论失败"), "none",
            _t('非中文评论操作'), "如果评论中不包含中文，则执行该操作");
        $form->addInput($opt_no_chinese);
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

        // 执行操作
        if ($opt == "abandon") {
            Typecho_Cookie::set('__typecho_remember_text', $comment['text']);
            throw new Typecho_Widget_Exception($error);
        } else if ($opt == "waiting") {
            $comment['status'] = 'waiting';
        }
        Typecho_Cookie::delete('__typecho_remember_text');
        return $comment;
    }

    private static function check_in($words_str, $str)
    {
        $words = explode("\n", $words_str);
        if (empty($words)) {
            return false;
        }
        foreach ($words as $word) {
            if (false !== strpos($str, trim($word))) {
                return true;
            }
        }
        return false;
    }
}
