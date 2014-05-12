<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<title>Passnote</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no">
<link rel="stylesheet" type="text/css" href="/css/style.css">
<link rel="icon shortcut" href="/favicon.png" type="image/png">
<link rel="apple-touch-icon" href="/img/mobius-256.png" />

{#
<script src="/components/requirejs/require.js"></script>
<script src="/js/amd.config.js"></script>
<script>
	requirejs.config( {
		baseUrl: '/js',
		waitSeconds: 20
	} );

	require(["passnote"])
</script>
#}

</head>

<body>
	{{ content() }}
</body>

</html>