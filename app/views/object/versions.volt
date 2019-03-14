<h2 class="obj-title">{{ object.title | e }}</h2>
{%  if object.description  %}<p><em>{{ object.description | e }}</em></p>{% endif %}
<hr/>

{% for version in versions %}
<div class="obj-raw">
	<div class="header h-clearfix">
		<h4 class="title">
			{{ version.getDateCreated('j M Y, g:ia') }}
		</h4>
		<div class="right">
			{% if loop.first %}
				(current version)
			{% else %}
				<a class="obj-raw-action danger" href="/object/{{ object.getUuid() | escape_attr }}/delete/{{ version.getUuid() | escape_attr }}">Delete</a>
				{#<a class="obj-raw-action" href="/object/{{ object.getUuid() | escape_attr }}/versions/{{ version.getUuid() | escape_attr }}">View</a>#}
			{% endif %}
		</div>
	</div>
	<pre>{{ version._diff }}</pre>
</div>
	{% else %}
	<p>There are no historic versions of this object.</p>
{% endfor %}
