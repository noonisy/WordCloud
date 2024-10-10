# WordCloud 
词云生成器插件

WordCloud 是一个为 Typecho 系统开发的词云生成器插件。它可以根据你的博客文章内容，生成 echarts 类型的词云图，展示网站中常用的中英文词汇

## Display Effect

https://github.com/user-attachments/assets/d413a15a-9124-4380-9300-97179827fafc

## Features

- 自动分析博客文章内容，提取关键词
- 可自定义词云的形状、大小、字体等多个参数
- 支持自定义词频和忽略特定词语
- 使用 ECharts 库生成交互式词云图
- 支持本地浏览器缓存，提高加载速度

## Installation

1. 下载本插件，解压后重命名文件夹为 `WordCloud`
2. 将 `WordCloud` 文件夹上传到你的Typecho站点的 `/usr/plugins/` 目录下
3. 登录后台，在 "控制台" > "插件" 中找到 "WordCloud 词云生成器"，点击 "启用"

## Usage

1. 在插件设置页面，根据需要调整各项参数
2. 在你想要显示词云的模板文件中（如 `page-tags.php`），添加以下代码:

   ```php
    // 前提引入了 $plugins
    $plugins = Typecho_Plugin::export();

    // 示例代码
    <?php if (isset($plugins['activated']['WordCloud'])){ ?>
        <div class="mdui-card-primary-title">词云</div>
        <div class="mdui-card-primary-subtitle">共计<?php echo WordCloud_Plugin::getNumWords(); ?>个词</div>
        <?php echo "<script>
        $(document).on('pjax:popstate', function (event) {
            if (event.currentTarget.URL.endsWith('/tag.html')) {
                event.preventDefault();
                $.pjax.defaults.maxCacheLength = 0;
                location = event.currentTarget.URL;
            }
        });
        </script>";
        WordCloud_Plugin::renderWordCloud(); ?>
    <?php } ?>
   ```

3. 保存并访问相应页面，即可看到生成的词云

## Configuration

插件提供了多个可自定义的选项:

- 展示的文本数量: 设置词云中显示的词语数量
- 词云形状: 可选择正方形、圆形、三角形等多种形状
- 宽度和高度: 设置词云的尺寸
- 字体大小范围: 设置词语的最小和最大字体大小
- 旋转设置: 控制词语的旋转角度和变化幅度
- 网格大小: 调整词语之间的间距
- 边界渲染: 是否允许词语在边界外渲染
- 文本收缩: 是否允许收缩文本以适应空间
- 窗口大小改变时重新生成: 控制响应式行为
- 分词预处理: 是否在每次生成时重新处理分词
- 本地缓存: 设置浏览器缓存的过期时间
- 自定义词频: 手动设置特定词语的出现频率
- 忽略词语: 设置不希望出现在词云中的词语

## Notes

- 首次加载可能需要一些时间来处理文章内容和生成词云
- 如果博客文章内容较多，建议适当增加 "本地浏览器缓存过期时间"
- 反复跳转时，可能会不显示

## Technical Support

如果你在使用过程中遇到任何问题，或有任何建议，请访问 [个人主页](https://www.noonisy.com) 或在 GitHub 上提交 issue 和 PR

## Copyright

- WordCloud 词云生成器插件由 [Noonisy](https://www.noonisy.com) 开发
- 版本 1.0.0
- 使用MIT许可证
