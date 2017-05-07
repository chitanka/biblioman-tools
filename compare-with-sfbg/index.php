<!DOCTYPE html>
<html lang="bg">
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>Сравняване на книги между Библиоман и SFBG</title>

	<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
</head>

<style>
	h2.number {
		font-size: 600%;
		text-decoration: underline double silver;
	}
	h3 {
		font-size: 130%;
	}
	hr {
		border: 0;
		height: 8px;
		background: #ddd;
		background-image: linear-gradient(to right, white, #ccc, white);
		margin: 3em 1em;
	}
	.same {
		background-color: lightgreen;
	}
	.different {
		background-color: lightsalmon;
	}
</style>
<body>
<div class="container">

	<div class="page-header">
		<h1>Сравняване на книги между Библиоман и SFBG</h1>
	</div>

	<?php $pageSize = filter_input(INPUT_GET, 'size') ?: 25 ?>
	<?php $data = file(__DIR__.'/data.csv') ?>
	<?php $page = filter_input(INPUT_GET, 'page') ?: 1 ?>
	<?php $offset = $page * $pageSize - $pageSize ?>
	<?php foreach (array_slice($data, $offset, $pageSize) as $i => $row): ?>
		<?php
		list($bibliomanId, $sfbgSlug, $bibliomanTitle, $sfbgTitle, $bibliomanAuthor, $sfbgAuthor, $sfbgCover) = explode("\t", trim($row));
		?>
	<div class="row record" data-ids="<?= $bibliomanId ?>-<?= $sfbgSlug ?>">
		<div class="col-md-2">
			<h2 class="number"><?= ($offset + $i+1) ?></h2>
		</div>
		<div class="col-md-4">
			<h3><?= $bibliomanTitle, ' – ', $bibliomanAuthor ?></h3>
			<a class="thumbnail" href="//biblioman.chitanka.info/books/<?= $bibliomanId ?>" target="_blank">
				<img src="//biblioman.chitanka.info/books/<?= $bibliomanId ?>.cover?size=150">
			</a>
		</div>
		<div class="col-md-4">
			<h3><?= $sfbgTitle, ' – ', $sfbgAuthor ?></h3>
			<a class="thumbnail" href="http://sfbg.us/book/<?= $sfbgSlug ?>" target="_blank">
				<img src="http://sfbg.us/<?= $sfbgCover != 'none' && $sfbgCover != '' ? "covers/$sfbgCover" : 'img/nocl.png' ?>" width="150">
			</a>
		</div>
		<div class="col-md-2">
			<a href="#" class="btn btn-success mark" data-class="same" style="display: block; margin-top: 5em">Еднакви са</a>
			<a href="#" class="btn btn-danger mark" data-class="different" style="display: block; margin-top: 3em">Различни са</a>
		</div>
	</div>
	<hr>
	<?php endforeach ?>

	<nav class="text-center">
		<ul class="pagination">
			<?php $nbPages = ceil(count($data) / $pageSize) ?>
			<?php for ($p = 1; $p <= $nbPages; $p++): ?>
				<li class="<?= $p == $page ? 'active' : '' ?>"><a href="?page=<?= $p ?>"><?= $p ?></a></li>
			<?php endfor  ?>
		</ul>
	</nav>

	<div class="text-center">
		<a href="#" class="btn btn-info showResults">Показване на резултатите</a>
		<a href="#" class="btn btn-danger clearResults">Изчистване на въведените данни</a>
	</div>

</div> <!-- /container -->

<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.2.4/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

<script>
function classFormRecord($record) {
	return 'class-' + $record.data('ids');
}
$(function() {
	$(document).on("click", "a.mark", function() {
		var $record = $(this).closest(".record");
		var markedClass = $(this).data('class');
		localStorage.setItem(classFormRecord($record), markedClass);
		$record.removeClass('same different').addClass(markedClass);
		return false;
	});
	$('.record').each(function() {
		var $record = $(this);
		var markedClass = localStorage.getItem(classFormRecord($record));
		if (markedClass) {
			$record.addClass(markedClass);
		}
	});
	$('.showResults').on('click', function () {
		var results = $('.record.same').map(function () {
			return $(this).data('ids');
		}).get().join("\n");
		$('<textarea class="form-control" rows="10"></textarea>').html(results).insertBefore(this);
		return false;
	});
	$('.clearResults').on('click', function () {
		$('.record').removeClass('same different');
		localStorage.clear();
		return false;
	});
});
</script>
</body>
</html>
