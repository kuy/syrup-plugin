window.React = React = require 'react'
Flux = require('delorean').Flux
$ = jQuery

window.SyrupStore = SyrupStore = Flux.createStore

  scheme: {
    tags: {
      default: []
    },
    selected: {
      default: ['pickup']
    },
    reloading: {
      default: false
    }
  }

  actions: {
    'maps:reload': 'reload',
    'maps:done': 'done',
    'tag:set': 'setTags'
    'tag:select': 'selectTag',
    'tag:unselect': 'unselectTag',
    'tag:toggle': 'toggleTag'
  }

  reload: ->
    console.log "store: reload"
    @set 'reloading', true

  done: ->
    console.log "store: done"
    @set 'reloading', false

  setTags: (tags) ->
    console.log "store: set: #{tags}"
    @set 'tags', tags

  selectTag: (tag) ->
    console.log "store: select: #{tag}"
    @set 'selected', @state.selected.concat(tag)

  unselectTag: (tag) ->
    console.log "store: unselect: #{tag}"
    @set 'selected', (t for t in @state.selected when t isnt tag)

  toggleTag: (tag) ->
    console.log "store: toggle: #{tag}"
    @set 'selected', [tag]

SyrupDispatcher = Flux.createDispatcher

  reload: ->
    if not @stores['app'].reloading
      console.log "dispatch: reload"
      @dispatch 'maps:reload'

  done: ->
    if not @stores['app'].reloading
      console.log "dispatch: done"
      @dispatch 'maps:done'

  setTags: (tags) ->
    console.log "dispatch: set: #{tags}"
    @dispatch 'tag:set', tags

  selectTag: (tag) ->
    console.log "dispatch: select: #{tag}"
    @dispatch 'tag:select', tag

  unselectTag: (tag) ->
    console.log "dispatch: unselect: #{tag}"
    @dispatch 'tag:unselect', tag

  toggleTag: (tag) ->
    console.log "dispatch: toggle: #{tag}"
    @dispatch 'tag:toggle', tag

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

  setTags: (tags) ->
    console.log "action: set: #{tags}"
    SyrupDispatcher.setTags tags

  selectTag: (tag) ->
    console.log "action: select: #{tag}"
    SyrupDispatcher.selectTag tag

  unselectTag: (tag) ->
    console.log "action: unselect: #{tag}"
    SyrupDispatcher.unselectTag tag

  toggleTag: (tag) ->
    console.log "action: toggle: #{tag}"
    SyrupDispatcher.toggleTag tag

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

    <ul>
      {store.tags.map (tag) ->
        <li key={tag.id}>
          <label>
            <input type="checkbox" value={tag.slug} onChange={self.handleChange} defaultChecked={if tag.slug in store.selected then 'checked' else ''} />
            {tag.term_group + ':' + tag.name}
          </label>
        </li>
      }
    </ul>

AreaSelector = React.createClass

  mixins: [Flux.mixins.storeListener]

  handleClick: (e) ->
    SyrupActionCreator.toggleTag e.target.value
    do SyrupActionCreator.reload

  render: ->
    self = @
    store = @getStore('app')
    tags = (tag for tag in store.tags when tag.term_group == 'area')

    <div className="toggle">
      {tags.map (tag) ->
        <div className="toggle-item" key={tag.id}>
          <input type="radio" id={"area-#{tag.slug}"} name="area" value={tag.slug} onClick={self.handleClick} defaultChecked={if tag.slug in store.selected then 'checked' else ''} />
          <label htmlFor={"area-#{tag.slug}"}>{tag.name}</label>
        </div>
      }
    </div>

GoogleMaps = React.createClass

  mixins: [Flux.mixins.storeListener]

  componentDidMount: ->
    node = React.findDOMNode(@)
    @markers = []
    @map = new google.maps.Map node, {}

  storeDidChange: ->
    console.log "changed"
    self = @
    store = @getStore('app')

    if store.reloading
      console.log "start reloading"
      tags = store.selected.join(',')
      $.ajax {
        method: 'GET',
        url: ENDPOINT,
        data: {
          action: 'syrup_get_shops',
          tags: tags
        },
        success: (data) ->
          console.log data
          self.updateMarkers data.data
          do SyrupActionCreator.done
      }

  updateMarkers: (spots) ->
    do @clearMarkers
    return if not spots or spots.length == 0

    bounds = new google.maps.LatLngBounds()
    for spot in spots
      pos = new google.maps.LatLng parseFloat(spot.lat), parseFloat(spot.lng)
      bounds.extend pos
      marker = new google.maps.Marker { position: pos, map: @map, title: spot.name }
      @markers.push marker
      info = new google.maps.InfoWindow {
        content: """
          <div class="syrup-info">
            <h3><a href="#{spot.permalink}">#{spot.name}&#187;</a></h3>
          </div>
        """
      }

      google.maps.event.addListener marker, 'click', ((info, marker) ->
        -> info.open @map, marker
      )(info, marker)

    google.maps.event.addListenerOnce @map, 'bounds_changed', =>
      @map.setZoom 15 if 15 < @map.getZoom()

    @map.fitBounds bounds

  clearMarkers: ->
    for marker in @markers
      marker.setMap null

  render: ->
    <div className="syrup-map" />

SyrupApp = React.createClass

  mixins: [Flux.mixins.storeListener]

  componentDidMount: ->
    $.ajax {
      method: 'GET',
      url: ENDPOINT,
      data: {
        action: 'syrup_get_tags'
      },
      success: (data) ->
        console.log data
        tags = (tag for tag in data.data)
        SyrupActionCreator.setTags tags
        do SyrupActionCreator.reload
    }

  render: ->
    <div>
      <AreaSelector />
      <GoogleMaps />
    </div>

setTimeout ->
  window.mainView = React.render <SyrupApp dispatcher={SyrupDispatcher} />,
    document.getElementById('syrup-container')
, 500
