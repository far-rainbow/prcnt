<?php

/* @var $this yii\web\View */

$this->title = 'My Yii Application';
?>
<div class="site-index">

	<style>
	/* centered columns styles */
	.row-centered {
	    text-align:center;
	}
	.col-centered {
	    display:inline-block;
	    float:none;
	    /* reset the text-align */
	    text-align:left;
	    /* inline-block space fix */
	    margin-right:-4px;
	}
	.col-min {
    /* custom min width */
    min-width:320px;
	}
	.item {
    width:100%;
    height:100%;
	border:1px solid #cecece;
    padding:16px 8px;
	background:#ededed;
	background:-webkit-gradient(linear, left top, left bottom,color-stop(0%, #f4f4f4), color-stop(100%, #ededed));
	background:-moz-linear-gradient(top, #f4f4f4 0%, #ededed 100%);
	background:-ms-linear-gradient(top, #f4f4f4 0%, #ededed 100%);
	}
	.content {
	display:table-cell;
	vertical-align:middle;
	text-align:center;
	}
	</style>

    <div class="body-content">

		<?=
		Yii::$app->user->isGuest ? (
		    '<div class="jumbotron">'
		        .'<h1>PRCNT</h1>'
		        .'<p class="lead">Нажмите Login для входа</p>'
		    .'</div>'
		) : (
		
		$this->render('admin')
		
		)
		?>
        
	</div>

</div>
