<?php
/**
 * 基于Typecho的RSS插件
 * 
 * @package AllRSS
 * @author sun <i@imsun.org>
 * @version 1.0
 * @link https://imsun.org
 */
class AllRSS_Plugin extends Typecho_Widget implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        // 注册路由
        Helper::addRoute('allrss_all', '/rss', 'AllRSS_Plugin', 'outputAllPostsRSS');


        return _t('插件已激活');
    }
    

    public static function deactivate()
    {
        // 移除路由
        Helper::removeRoute('allrss_all');


        return _t('插件已停用');
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 插件配置页面
        $enableCache = new Typecho_Widget_Helper_Form_Element_Radio(
            'enableCache',
            array('1' => _t('启用缓存'), '0' => _t('禁用缓存')),
            '0',
            _t('缓存设置'),
            _t('是否启用RSS缓存')
        );
        $form->addInput($enableCache);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
        // 个人配置页面
    }

    public function execute()
    {
        // 实现 execute 方法
    }

    public static function outputAllPostsRSS()
    {
        header('Content-Type: application/rss+xml; charset=UTF-8');
        $db = Typecho_Db::get();
        $posts = $db->fetchAll($db->select()->from('table.contents')
            ->where('type = ?', 'post')
            ->where('status = ?', 'publish')
            ->order('created', Typecho_Db::SORT_DESC));

        self::outputRSS($posts);
    }



    private static function outputRSS($posts, $titleSuffix = '')
    {
        $options = Typecho_Widget::widget('Widget_Options');
        
        echo '<?xml version="1.0" encoding="UTF-8"?>';
        echo '<rss version="2.0">';
        echo '<channel>';
        echo '<title>' . htmlspecialchars($options->title . ' - ' . $titleSuffix, ENT_QUOTES, 'UTF-8') . '</title>';
        echo '<link>' . htmlspecialchars($options->siteUrl, ENT_QUOTES, 'UTF-8') . '</link>';
        echo '<description>' . htmlspecialchars($options->description, ENT_QUOTES, 'UTF-8') . '</description>';

        foreach ($posts as $post) {
            $link = htmlspecialchars($post['permalink'], ENT_QUOTES, 'UTF-8');
            $title = htmlspecialchars($post['title'], ENT_QUOTES, 'UTF-8');
            $description = htmlspecialchars(strip_tags($post['text']), ENT_QUOTES, 'UTF-8');
            $pubDate = date(DATE_RSS, $post['created']);

            echo '<item>';
            echo '<title>' . $title . '</title>';
            echo '<link>' . $link . '</link>';
            echo '<guid>' . $link . '</guid>';
            echo '<description>' . $description . '</description>';
            echo '<pubDate>' . $pubDate . '</pubDate>';
            echo '</item>';
        }

        echo '</channel>';
        echo '</rss>';
        exit;
    }

    public static function addRSSLink()
    {
        // 在顶部菜单中添加 RSS 链接
        echo '<link rel="alternate" type="application/rss+xml" title="All Posts RSS" href="' . Helper::options()->siteUrl . 'rss" />';
    }
}

// 注册插件
Typecho_Plugin::factory('index.php')->footer = array('AllRSS_Plugin', 'addRSSLink');
