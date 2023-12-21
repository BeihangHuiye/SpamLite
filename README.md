最近饱受一个域名为snowcherryblossom.com的骚扰。评论也就只评论个域名。打开域名一看是什么垃圾网站，网站内容毫无观赏性可言。关于Typecho的评论过滤插件，之前推荐过百度审核（见文：《[本站评论已使用BaiduTextcensor对评论内容进行审核，可一年免费试用5万次](https://www.52txr.cn/2023/SpamLite.html)》），但是百度审核还真不能过滤这玩意。于是我找了一个名为SmartSpam的插件，但是感觉这个插件做的有点太复杂了，对于很多小白选手的简单需求反而起了副作用，因为不能正确配置这个插件导致误杀正常评论是很几个网站朋友跟我说的。于是我在这个基础上进行了简化，返璞归真，回到评论最本质的还是评论的内容。我对乱填邮箱之类的行为还是包容的，毕竟互联网上有的人就是不愿意暴露马甲。


## 0.0.2版本更新

除了0.01对评论内容进行了敏感词过滤，新增了对昵称、网址、邮箱的过滤！基本上是完全满足了对无聊者的评论过滤。

![image](https://github.com/BeihangHuiye/SpamLite/assets/148823447/238ad03a-75c6-4eaf-857a-38e0c10006bb)


## 遭遇的垃圾评论


![image](https://github.com/BeihangHuiye/SpamLite/assets/148823447/a187535b-5a65-482f-a099-07aa5f525191)

## 插件下载

简化版的插件已经在Github上开源.

我的博客环境：Typecho1.2.1、PHP8.2、HandSome9.0.2


这里我也给了蓝奏云的下载链接：[SpamLite插件 -蓝奏云下载](https://wwtx.lanzout.com/iJ0PA1ifu5qb)


## 插件使用

和所有的Typecho插件一样，上传到插件目录。记得确保插件目录的名称为SpamLite。

插件的配置很简单。就两个筛选条件：敏感词汇和非中文评论。

![image](https://github.com/BeihangHuiye/SpamLite/assets/148823447/e36554e6-ef84-48ab-8244-7cd63a5bb45b)

## 插件测试

评论中带有敏感词的话，则会直接提示评论失败。

![image](https://github.com/BeihangHuiye/SpamLite/assets/148823447/acce7dec-306f-4542-b422-41073c3bf923)

如果是非中文评论，则会进入待审核状态：

![image](https://github.com/BeihangHuiye/SpamLite/assets/148823447/41a3d224-593f-4152-b21e-4f3c1ff693e2)

## SmartSpam下载地址

特别感谢原作者。我这差不多是直接抄的原作者的代码：

[Typecho智能评论过滤插件：SmartSpam](https://www.yovisun.com/archive/typecho-plugin-smartspam.html)




