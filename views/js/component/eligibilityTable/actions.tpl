{{#if allowedToWrite}}
    <button class="btn-info small edit" title="{{__ 'Edit eligibile test takers'}}" data-action="edit">
        <span class="icon-edit"></span> {{__ 'Edit'}}
    </button>

    {{#if byPassProctor}}
        <button class="btn-info small edit" title="{{__ 'Enable proctor authorization'}}" data-action="shield">
            <span class="icon-shield"></span> {{__ 'Require authorization'}}
        </button>
    {{else}}
        <button class="btn-info small edit" title="{{__ 'Remove proctor authorization'}}" data-action="unshield">
            <span class="icon-unshield"></span> {{__ 'Monitor only'}}
        </button>
    {{/if}}

    <button class="btn-info small remove" title="{{__ 'Remove eligibility'}}" data-action="remove">
        <span class="icon-bin"></span> {{__ 'Remove'}}
    </button>
{{/if}}

