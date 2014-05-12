<div class="fill-frame">
	<div class="nav-header">
		<div class="container sch-container h-clearfix">
			<form class="row" action="/object/find" method="post">
				<div class="sch-form col-md-12">
					<div class="sch-search-cont">
						<a href="/" class="sch-logo">△</a>
						<input type="search" name="query">
					</div>
					<div class="sch-submit-cont">
						<input type="submit" class="pair-left" value="Search" spellcheck="disable" autocomplete="disable">
					</div>
					<div class="h-clear"></div>
				</div>
			</form>
		</div>
	</div>

	<div class="container sch-container">
		{% block content %}
			<p>Please override the content block.</p>
		{% endblock %}
	</div>
</div>