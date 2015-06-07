window.React = React = require 'react'
Flux = require('delorean').Flux
$ = jQuery

window.SyrupStore = SyrupStore = Flux.createStore

  scheme: {
    selected: {
      default: ['cafe', 'shibuya']
    },
    reloading: {
      default: false
    }
  }

  actions: {
    'maps:reload': 'reload',
    'maps:done': 'done',
    'tag:select': 'selectTag',
    'tag:unselect': 'unselectTag'
  }

  reload: ->
    console.log "store: reload"
    @set 'reloading', true

  done: ->
    console.log "store: done"
    @set 'reloading', false

  selectTag: (tag) ->
    console.log "store: select: #{tag}"
    @set 'selected', @state.selected.concat(tag)

  unselectTag: (tag) ->
    console.log "store: unselect: #{tag}"
    @set 'selected', (t for t in @state.selected when t isnt tag)

SyrupDispatcher = Flux.createDispatcher

  reload: ->
    if not @stores['app'].reloading
      console.log "dispatch: reload"
      @dispatch 'maps:reload'

  done: ->
    if not @stores['app'].reloading
      console.log "dispatch: done"
      @dispatch 'maps:done'

  selectTag: (tag) ->
    console.log "dispatch: select: #{tag}"
    @dispatch 'tag:select', tag

  unselectTag: (tag) ->
    console.log "dispatch: unselect: #{tag}"
    @dispatch 'tag:unselect', tag

  getStores: ->
    {
      app: SyrupStore
    }

SyrupActionCreator =

  reload: ->
    console.log "action: reload"
    do SyrupDispatcher.reload

  done: ->
    console.log "action: done"
    do SyrupDispatcher.done

  selectTag: (tag) ->
    console.log "action: select: #{tag}"
    SyrupDispatcher.selectTag tag

  unselectTag: (tag) ->
    console.log "action: unselect: #{tag}"
    SyrupDispatcher.unselectTag tag

TagSelector = React.createClass

  mixins: [Flux.mixins.storeListener]

  handleChange: (e) ->
    if e.target.checked
      SyrupActionCreator.selectTag e.target.value
    else
      SyrupActionCreator.unselectTag e.target.value

    do SyrupActionCreator.reload

  render: ->
    self = @
    store = @getStore('app')
    tags = ['cafe', 'tokyo', 'shibuya', 'omotesando']

    <ul>
      {tags.map (tag) ->
        <li>
          <label>
            <input type="checkbox" value={tag} onChange={self.handleChange} defaultChecked={if tag in store.selected then 'checked' else ''} />
            {tag}
          </label>
        </li>
      }
    </ul>

GoogleMaps = React.createClass

  mixins: [Flux.mixins.storeListener]

  componentDidMount: ->
    map = React.findDOMNode(@)

  storeDidChange: ->
    console.log "changed"
    store = @getStore('app')
    if store.reloading
      console.log "start reloading"
      tags = store.selected.join('+')
      $.ajax {
        method: 'GET',
        url: ENDPOINT,
        data: {
          action: 'syrup_get_shops',
          tags: tags
        },
        success: (data) ->
          console.log data
          do SyrupActionCreator.done
      }

  render: ->
    <div className="maps" />

SyrupApp = React.createClass

  mixins: [Flux.mixins.storeListener]

  componentDidMount: ->
    do SyrupActionCreator.reload

  render: ->
    <div>
      <TagSelector />
      <GoogleMaps />
    </div>

setTimeout ->
  window.mainView = React.render <SyrupApp dispatcher={SyrupDispatcher} />,
    document.getElementById('syrup-container')
, 500
