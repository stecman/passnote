<div class="fill-frame">
	<div class="nav-header">
		<div class="container sch-container h-clearfix">
			<form class="row" action="/" method="post">
				<div class="sch-form col-md-12">
					
					<div class="sch-search-cont">
						<a href="/" class="sch-logo">△</a>
						<input type="search" name="query" value="{{ request.getPost('query', 'escape_attr') }}" {% if search_autofocus is defined %}autofocus{% endif %}>
					</div>

					<div class="sch-submit-cont">
						<input type="submit" class="pair-left" value="Search" spellcheck="disable" autocomplete="disable">
					</div>

					<div class="h-clear"></div>
				</div>
			</form>

		</div>
	</div>

	<div class="nav-top h-clearfix">
	<div class="container sch-container">

		<div class="row">
			<div class="col-md-12">
				<a href="/object/new/edit">New Object</a>
				<a href="/auth/logout" class="right">Logout</a>
				<a href="/account" class="right">Account</a>
			</div>
		</div>
	</div>
	</div>

	{% block precontent %}{% endblock %}

	<div class="container sch-container">
		{{ flashSession.output() }}
		{% block content %}
			<p>Please override the content block.</p>
		{% endblock %}
	</div>
</div>
