<?php
if (!defined('__TYPECHO_ROOT_DIR__')) exit;

/**
 * 词云生成器
 * 
 * @package WordCloud
 * @author Noonisy
 * @version 1.0.0
 * @link https://www.noonisy.com
 */
class WordCloud_Plugin implements Typecho_Plugin_Interface
{
    public static $sortedWords = null;

    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
        return '词云生成器插件已启用';
    }

    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
    {
        return '词云生成器插件已禁用';
    }
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // 展示的文本数量
        $numWords = new Typecho_Widget_Helper_Form_Element_Text(
            'numWords',
            null,
            '288',
            _t('展示的文本数量'),
            _t('设置要在词云中显示的文本数量')
        );
        $form->addInput($numWords);
        
        // 形状
        $shape = new Typecho_Widget_Helper_Form_Element_Radio(
            'shape',
            array(
                'square' => '正方形',
                'circle' => '圆形',
                'triangle' => '三角形',
                'diamond' => '菱形',
                'triangle-upright' => '三角形向上',
                'triangle-forward' => '三角形向前',
                'star' => '星形',
                'pentagon' => '五边形',
                'hexagon' => '六边形',
            ),
            'square',
            _t('展示的词云形状'),
            _t('设置要在词云中显示的单词形状')
        );
        $form->addInput($shape);

        # 宽度
        $width = new Typecho_Widget_Helper_Form_Element_Text(
            'width',
            null,
            '75%',
            _t('宽度'),
            _t('设置词云的宽度，默认75%')
        );
        $form->addInput($width);

        // 高度
        $height = new Typecho_Widget_Helper_Form_Element_Text(
            'height',
            null,
            '80%',
            _t('高度'),
            _t('设置词云的高度，默认80%')
        );
        $form->addInput($height);

        // 最小字体大小
        $minFontSize = new Typecho_Widget_Helper_Form_Element_Text(
            'minFontSize',
            null,
            '15',
            _t('最小字体大小'),
            _t('设置文本中最小的字体大小，默认12px')
        );
        $form->addInput($minFontSize);

        // 最大字体大小
        $maxFontSize = new Typecho_Widget_Helper_Form_Element_Text(
            'maxFontSize',
            null,
            '40',
            _t('最大字体大小'),
            _t('设置文本中最大的字体大小，默认60px')
        );
        $form->addInput($maxFontSize);

        // 最大旋转度数
        $rotationRange = new Typecho_Widget_Helper_Form_Element_Text(
            'rotationRange',
            null,
            '90',
            _t('最大旋转度数'),
            _t('设置文本最大旋转度数')
        );
        $form->addInput($rotationRange);

        // 旋转角度的最小变化度数
        $rotationStep = new Typecho_Widget_Helper_Form_Element_Text(
            'rotationStep',
            null,
            '10',
            _t('旋转角度的最小变化度数'),
            _t('设置文本旋转角度的最小变化幅度，数值越小，旋转角度的变化角度越多')
        );
        $form->addInput($rotationStep);

        // 网格大小
        $gridSize = new Typecho_Widget_Helper_Form_Element_Text(
            'gridSize',
            null,
            '8',
            _t('网格大小'),
            _t('设置网格大小，数值越大，文本之间的间距越大，默认8px')
        );
        $form->addInput($gridSize);

        // 是否允许词云在边界外渲染
        $drawOutOfBound = new Typecho_Widget_Helper_Form_Element_Select(
            'drawOutOfBound',
            array(
                'true' => '是',
                'false' => '否'
            ),
            'false',
            _t('是否允许词云在边界外渲染'),
            _t('设置是否允许词云在边界外渲染，如果设置为true容易造成词重叠')
        );
        $form->addInput($drawOutOfBound);

        # 是否收缩文本
        $shrinkToFit = new Typecho_Widget_Helper_Form_Element_Select(
            'shrinkToFit',
            array(
                'true' => '是',
                'false' => '否'
            ),
            'false',
            _t('收缩文本'),
            _t('设置是否收缩文本，如果设置为true，则文本将被缩小，默认不收缩')
        );
        $form->addInput($shrinkToFit);

        // 是否窗口大小改变时重新生成词云
        $chartResize = new Typecho_Widget_Helper_Form_Element_Select(
            'chartResize',
            array(
                'true' => '是',
                'false' => '否'
            ),
            'false',
            _t('窗口大小改变时重新生成词云'),
            _t('设置是否窗口大小改变时重新生成词云')
        );
        $form->addInput($chartResize);

        // 是否重新预处理分词
        $reprocessWords = new Typecho_Widget_Helper_Form_Element_Select(
            'reprocessWords',
            array(
                'true' => '是',
                'false' => '否'
            ),
            'false',
            _t('重新预处理分词'),
            _t('设置是否在每次生成词云时重新预处理分词，可能会影响性能')
        );
        $form->addInput($reprocessWords);

        //本地浏览器缓存过期时间
        $localCacheExpire = new Typecho_Widget_Helper_Form_Element_Text(
            'localCacheExpire',
            null,
            '1000 * 60 * 60 * 24',
            _t('本地浏览器缓存过期时间'),
            _t('设置本地浏览器缓存过期时间，单位为毫秒')
        );
        $form->addInput($localCacheExpire);

        # 自定义某个词出现的数量
        $customWordCount = new Typecho_Widget_Helper_Form_Element_Textarea(
            'customWordCount',
            null,
            'noonisy:668',
            _t('自定义词出现的数量'),
            _t('设置某个词出现的数量，格式为："词:数量"，每行一个')
        );
        $form->addInput($customWordCount);

        // 需要忽略的词
        $ignoreWords = new Typecho_Widget_Helper_Form_Element_Textarea(
            'ignoreWords',
            null, 
            '的\n是\n在',
            _t('需要忽略的词'), 
            _t('设置要在词云中忽略的词，每行一个<hr>
        <div style="font-family:consolas; background:#E8EFD1; padding:8px">在合适的地方，例如 page-tags.php 加入代码: <br> <b style="color:#ec5072"> $plugins = Typecho_Plugin::export(); </b> <br> 后面再加入代码: <br>
            <b style="color:#ec5072">&lt;?php if (isset($plugins[\'activated\'][\'WordCloud\'])) { ?>
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&lt;div>共计&lt;?php echo WordCloud_Plugin::getNumWords(); ?>个词&lt;/div>
            <br>
            &nbsp;&nbsp;&nbsp;&nbsp;&lt;?php WordCloud_Plugin::renderWordCloud(); ?>
            <br>
            &lt;?php } ?> </b>
            </div>')
        );
        $form->addInput($ignoreWords);
    }

    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {
    }

    /**
     * 获取词云中显示的文本数量
     * 
     * @access public
     * @return int
     */
    public static function getNumWords()
    {
        $options = Helper::options()->plugin('WordCloud');
        return $options->numWords;
    }

    /**
     * 渲染词云
     * 
     * @access public
     * @return void
     */
    public static function renderWordCloud()
    {
        $options = Helper::options()->plugin('WordCloud');

        if (self::$sortedWords === null) {
            $db = Typecho_Db::get();
            $posts = $db->fetchAll($db->select('text')
                ->from('table.contents')
                ->where('type = ?', 'post')
                ->where('status = ?', 'publish')
                ->where('password IS NULL OR password = ""')
            );

            $allContent = '';
            foreach ($posts as $post) {
                $allContent .= ' ' . $post['text'];
            }

            $allContent = json_encode(['content' => $allContent]);

            echo '<div id="wordcloud" style="width: 100%; height: 500px; margin: 0 auto;"></div>';
            echo '<script src="https://cdn.jsdelivr.net/npm/echarts@5/dist/echarts.min.js"></script>';
            echo '<script src="https://cdn.jsdelivr.net/npm/echarts-wordcloud@2.1.0/dist/echarts-wordcloud.min.js"></script>';
            // // https://cdn.jsdelivr.net/npm/segmentit@2.0.3/dist/umd/segmentit.min.js
            echo '<script src="/usr/plugins/WordCloud/segmentit.min.js"></script>';

            echo '<script>
                // 预处理文本函数
                function processText(text) {
                    text = text.replace(/\n/g, " ");
                    text = text.replace(/<[^>]*>/g, "");
                    text = text.replace(/(\$\$.*?\$\$|\$.*?\$|\\[a-zA-Z]+|\\\S+|\$\S+|&quot;|&lt;|&gt;|&amp;)/g, "");
                    text = text.replace(/[^\u4e00-\u9fa5a-zA-Z\s]/g, " ")
                        .replace(/\b[a-zA-Z]{1,2}\b/g, " ");
                    text = text.split(/\s+/).filter(word => word.trim().length > 0).join(" ");
                    text = text.split(" ").map(word => {
                        if (/[\u4e00-\u9fa5]/.test(word) && word.length > 20) {
                            return word.match(/.{1,10}/g).join(" ");
                        }
                        return word;
                    }).join(" ");
                    return text;
                }

                // 分词函数
                function segmentText(text) {
                    const segmentit = Segmentit.useDefault(new Segmentit.Segment());
                    const result = segmentit.doSegment(text);
                    return result.map(item => item.w);
                }

                // 监听文档加载完成事件
                document.addEventListener("DOMContentLoaded", function() {
                    var needProcess = true;
                    var sortedWords = sessionStorage.getItem("sortedWords");
                    var sortedWordsExpire = sessionStorage.getItem("sortedWordsExpire");
                    
                    if (sortedWords && sortedWordsExpire) {
                        if (parseInt(sortedWordsExpire) > Date.now()) {
                            needProcess = false;
                        }
                    }
                    
                    if (' . $options->reprocessWords . ' || needProcess) {
                        var processedText = processText(' . $allContent . '.content);
                        var words = segmentText(processedText);

                        var wordCount = {};
                        words.forEach(function(word) {
                            if (word in wordCount) {
                                wordCount[word]++;
                            } else {
                                wordCount[word] = 1;
                            }
                        });

                        sortedWords = Object.keys(wordCount).map(function(word) {
                            return { name: word, value: wordCount[word] };
                        }).sort(function(a, b) {
                            return b.value - a.value;
                        });

                        var customWordCount = {};
                        var customWords = ' . json_encode($options->customWordCount) . '.split(\'\n\');
                        customWords.forEach(function(item) {
                            var parts = item.trim().split(\':\');
                            if (parts.length === 2) {
                                customWordCount[parts[0].trim()] = parseInt(parts[1].trim(), 10);
                            }
                        });
                        
                        sortedWords = sortedWords.filter(function(word) {
                            return ' . json_encode($options->ignoreWords) . '.indexOf(word.name) === -1;
                        }).map(function(word) {
                            if (customWordCount.hasOwnProperty(word.name)) {
                                word.value = customWordCount[word.name];
                            }
                            return word;
                        });
                        
                        for (var customWord in customWordCount) {
                            if (!sortedWords.some(word => word.name === customWord)) {
                                sortedWords.push({ name: customWord, value: customWordCount[customWord] });
                            }
                        }
                        
                        sortedWords = sortedWords.sort(function(a, b) {
                            return b.value - a.value;
                        }).slice(0, ' . $options->numWords . ');

                        sessionStorage.setItem("sortedWords", JSON.stringify(sortedWords));
                        sessionStorage.setItem("sortedWordsExpire", Date.now() + ' . $options->localCacheExpire . ');
                    } else {
                        sortedWords = JSON.parse(sessionStorage.getItem("sortedWords"));
                    }

                    var chart = echarts.init(document.getElementById("wordcloud"));
                    var option = {
                        tooltip: {},
                        series: [{
                            type: "wordCloud",
                            shape: "' . $options->shape . '",
                            left: "center",
                            top: "center",
                            width: "' . $options->width . '",
                            height: "' . $options->height . '",
                            right: null,
                            bottom: null,
                            sizeRange: [' . $options->minFontSize . ', ' . $options->maxFontSize . '],
                            rotationRange: [-' . $options->rotationRange . ', ' . $options->rotationRange . '],
                            rotationStep: ' . $options->rotationStep . ',
                            gridSize: ' . $options->gridSize . ',
                            drawOutOfBound: ' . $options->drawOutOfBound . ',
                            shrinkToFit: ' . $options->shrinkToFit . ',
                            textStyle: {
                                fontFamily: "sans-serif",
                                fontWeight: "bold",
                                color: function () {
                                    return "rgb(" + [
                                        Math.round(Math.random() * 160),
                                        Math.round(Math.random() * 160),
                                        Math.round(Math.random() * 160)
                                    ].join(",") + ")";
                                }
                            },
                            emphasis: {
                                textStyle: {
                                    shadowBlur: 10,
                                    shadowColor: "#333"
                                }
                            },
                            data: sortedWords
                        }]
                    };
                    chart.setOption(option);
                    if(' . $options->chartResize . ') {
                        window.addEventListener("resize", function() {
                            chart.resize();
                        });
                    }
                });
            </script>';
        }
    }
}