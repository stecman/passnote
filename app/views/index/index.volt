{{ content() }}

<div class="row">
	<div class="col-md-12 obj-list">
		{% for object in objects.getPaginate().items %}
		<a href="/object/{{ object.id | escape_attr }}">
			<h2 class="title">{{ object.title | e }}</h2>
			<p class="meta">
				<span class="date" title="{{ object.getDateCreated('j M Y, g:ia') }}">{{ object.getDateCreated('j M Y') }}</span>
				{{ object.description | e }}
			</p>
		</a>
		{% endfor %}
	</div>
</div>

<div class="row">
	<div id="note_content" class="ned-editor ace-tm"></div>
</div>