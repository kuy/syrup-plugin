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
    now: {
      default: 'on'
    },
    shops: {
      default: []
    }
  }

  actions: {
    'now:toggle': 'toggleNow',
    'tag:select': 'selectTag',
    'tag:unselect': 'unselectTag',
    'tag:toggle': 'toggleTag',
    'tag:load': 'loadTags',
    'shop:search': 'searchShops'
  }

  selectTag: (tag) ->
    console.log "store: selectTag: #{tag}"
    @set 'selected', @state.selected.concat(tag)

    do SyrupActionCreator.searchShops

  unselectTag: (tag) ->
    console.log "store: unselectTag: #{tag}"
    @set 'selected', (t for t in @state.selected when t isnt tag)

    do SyrupActionCreator.searchShops

  toggleTag: (tag) ->
    console.log "store: toggleTag: #{tag}"
    @set 'selected', [tag]

    do SyrupActionCreator.searchShops

  loadTags: ->
    console.log 'store: loadTags'

    $.ajax {
      method: 'GET',
      url: ENDPOINT,
      data: {
        action: 'syrup_get_tags'
      },
      success: (data) =>
        console.log "store: loadTags: loaded"
        @set 'tags', (tag for tag in data.data)

        do SyrupActionCreator.searchShops
    }

  toggleNow: ->
    console.log "store: toggleNow"
    @set 'now', (if @state.now == 'on' then 'off' else 'on')

    do SyrupActionCreator.searchShops

  searchShops: ->
    console.log 'store: searchShops'

    $.ajax {
      method: 'GET',
      url: ENDPOINT,
      data: {
        action: 'syrup_get_shops',
        tags: @state.selected.join(','),
        now: @state.now
      },
      success: (data) =>
        console.log "store: searchShops: loaded"
        @set 'shops', data.data
    }

SyrupDispatcher = Flux.createDispatcher

  selectTag: (tag) ->
    console.log "dispatch: selectTag: #{tag}"
    @dispatch 'tag:select', tag

  unselectTag: (tag) ->
    console.log "dispatch: unselectTag: #{tag}"
    @dispatch 'tag:unselect', tag

  toggleTag: (tag) ->
    console.log "dispatch: toggleTag: #{tag}"
    @dispatch 'tag:toggle', tag

  loadTags: ->
    console.log "dispatch: loadTags"
    @dispatch 'tag:load'

  toggleNow: ->
    console.log "dispatch: toggleNow"
    @dispatch 'now:toggle'

  searchShops: ->
    console.log "dispatch: searchShops"
    @dispatch 'shop:search'

  getStores: ->
    {
      app: SyrupStore
    }

SyrupActionCreator =

  selectTag: (tag) ->
    console.log "action: selectTag: #{tag}"
    SyrupDispatcher.selectTag tag

  unselectTag: (tag) ->
    console.log "action: unselectTag: #{tag}"
    SyrupDispatcher.unselectTag tag

  toggleTag: (tag) ->
    console.log "action: toggleTag: #{tag}"
    SyrupDispatcher.toggleTag tag

  loadTags: ->
    console.log "action: loadTags"
    do SyrupDispatcher.loadTags

  toggleNow: ->
    console.log "action: toggleNow"
    do SyrupDispatcher.toggleNow

  searchShops: ->
    console.log "action: searchShops"
    do SyrupDispatcher.searchShops

AreaSelector = React.createClass

  handleChange: (e) ->
    SyrupActionCreator.toggleTag e.target.value

  render: ->
    selected = if 0 < @props.selected.length then @props.selected[0] else ''
    areas = (tag for tag in @props.tags when tag.term_group == 'area')

    <select className="area-selector" onChange={@handleChange} value={selected}>
      {areas.map (area) ->
        <option key={area.id} value={area.slug}>{area.name}</option>
      }
    </select>

AreaCloudSelector = React.createClass

  mixins: [Flux.mixins.storeListener]

  handleClick: (e) ->
    SyrupActionCreator.toggleTag e.target.value

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

NowOption = React.createClass

  handleClick: (e) ->
    do SyrupActionCreator.toggleNow

  render: ->
    <div className="now-option"}}>
      <input type="checkbox" id="syrup-option-now" onChange={@handleClick} defaultChecked={if @props.now == 'on' then 'checked' else ''} />
      <label htmlFor="syrup-option-now">Now</label>
    </div>

ShopCardList = React.createClass

  render: ->
    <div className="pure-g shop-card-list">
      {@props.shops.map (shop) ->
        <ShopCard key={shop.id} shop={shop} />
      }
    </div>

ShopCard = React.createClass

  render: ->
    <div className="pure-u-1-2 pure-u-md-1-4 shop-card">
      <a href={@props.shop.post_url}>
        <img className="pure-img" src={@props.shop.thumbnail_url} />
      </a>
      <aside>
        <span>
          <a href={@props.shop.post_url}>
            {@props.shop.name}
          </a>
        </span>
      </aside>
    </div>

GoogleMaps = React.createClass

  mixins: [Flux.mixins.storeListener]

  componentDidMount: ->
    @shops = []
    node = React.findDOMNode(@)
    @markers = []
    @map = new google.maps.Map node, {
      panControl: false,
      mapTypeControl: false,
      scrollwheel: false,
      keyboardShortcuts: false
    }

  storeDidChange: (name) ->
    self = @
    store = @getStore name

    shop_ids = (shop.id for shop in store.shops)
    added_shops = (shop_id for shop_id in shop_ids when shop_id not in @shops)
    removed_shops = (shop_id for shop_id in @shops when shop_id not in shop_ids)

    if 0 < added_shops.length + removed_shops.length
      @updateMarkers store.shops

  updateMarkers: (shops) ->
    do @clearMarkers
    return if not shops or shops.length == 0

    bounds = new google.maps.LatLngBounds()
    for shop in shops
      pos = new google.maps.LatLng parseFloat(shop.lat), parseFloat(shop.lng)
      bounds.extend pos
      marker = new google.maps.Marker { position: pos, map: @map, title: shop.name }
      @markers.push marker
      info = new google.maps.InfoWindow {
        content: """
          <div class="syrup-info">
            <h3><a href="#{shop.post_url}">#{shop.name}&#187;</a></h3>
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
    do SyrupActionCreator.loadTags

  render: ->
    store = @getStore('app')

    <div>
      <div>
        <AreaSelector tags={store.tags} selected={store.selected} />
        <NowOption now={store.now} />
      </div>
      <GoogleMaps />
      <ShopCardList shops={store.shops} />
    </div>

setTimeout ->
  window.mainView = React.render <SyrupApp dispatcher={SyrupDispatcher} />,
    document.getElementById('syrup-container')
, 200
