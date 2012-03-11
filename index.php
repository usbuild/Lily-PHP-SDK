<html>
<head>
<title>API使用指南</title>
<?php
 require_once 'config.php';
 //global $Config;
 $dir = $Config->install_dir;
?>
</head>
<body>
	<h1>这是Lily-PHP-SDK的使用指南</h1>
        <h4><a href="<?php echo $dir?>/getTop10.php">获取十大帖子</a></h4>
        <h4><a href="<?php echo $dir?>/getHotBoard.php">获取热门板块</a></h4>
        <h4><a href="<?php echo $dir?>/getHotArticles.php">获取热门帖子</a></h4>
        <h4><a href="<?php echo $dir?>/getForums.php">获取分类版区</a></h4>
        <h4><a href="<?php echo $dir?>/getBoards.php?sec=0">获取分类帖子</a></h4>
        <h4><a href="<?php echo $dir?>/getPerson.php?username=comeonzqc">获取个人信息</a></h4>
        <h4><a href="<?php echo $dir?>/getCookie.php?username=null&password=null">获得Cookie</a></h4>
        <h4><a href="<?php echo $dir?>/logout.php?cookie=null">登出</a></h4>
        <h4><a href="<?php echo $dir?>/post.php?board=null&title=null&text=null&cookie=null">发表帖子</a></h4>
        <h4><a href="<?php echo $dir?>/postAfter.php?board=null&file=null&text=null&cookie=null">回复帖子</a></h4>
        <h4><a href="<?php echo $dir?>/uploadFile.php">上传文件[POST方式]</a></h4>
	<h4><a href="<?php echo $dir?>/getPosts.php?board=Pictures&start=null">获取某一版区的帖子</a></h4>
        <h4><a href="<?php echo $dir?>/getArticle.php?board=Pictures&file=M.1157981512.A&start=null">获取某一帖子</a></h4>
</body>
</html>