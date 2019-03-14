{% extends 'templates/app.volt' %}

{% block precontent %}
	{% if object is defined %}
	<div class="h-clearfix">
		<div class="container sch-container text-right nav-top nav-model">

			<div class="row">
				<div class="col-md-12">
					<a href="/object/{{ object.getUuid() | escape_attr }}">View</a>
					<a href="/object/{{ object.getUuid() | escape_attr }}/edit">Edit</a>
					<a href="/object/{{ object.getUuid() | escape_attr }}/versions">History</a>
				</div>
			</div>
		</div>
	</div>
	{%  endif %}
{% endblock %}

{% block content %}
	{{ content() }}
{% endblock %}
