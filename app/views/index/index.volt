{{ content() }}

<div class="row">
	<div class="col-md-12 obj-list">
		{% for object in objects %}
		<a href="/object/{{ object.id | escape_attr }}">
			<h2 class="title">{{ object.title | e }}</h2>
			<p class="meta">
				<span class="date" title="{{ object.getDateCreated('j M Y, g:ia') }}">{{ object.getDateCreated('j M Y') }}</span>
				{{ object.description | e }}
			</p>
		</a>
		{% else %}
			{% if search_term is defined %}
				<p>No objects found matching <em>"{{ search_term }}"</em></p>
			{% else %}
				<p>No objects found</p>
			{% endif %}
		{% endfor %}
	</div>
</div>