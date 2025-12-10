<?php

function h($str)
{
	return htmlspecialchars($str ?? "", ENT_QUOTES, "UTF-8");
}

function sel($value, $target)
{
	return $value === $target ? "selected" : "";
}
