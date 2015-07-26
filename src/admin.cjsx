window.React = React = require 'react'
Flux = require('delorean').Flux
$ = jQuery

window.Store = Store = Flux.createStore

  scheme: {
    hours: {
      default: []
    }
  }

  actions: {
    'hour:load': 'loadHours',
    'hour:add': 'addHour',
    'hour:remove': 'removeHour',
    'hour:update': 'updateHour'
  }

  loadHours: ->
    console.log 'store: loadHours'
    id = (new Date).getTime()
    hours = []
    for hour in window.Syrup.hours
      hour.open = @norm hour.open
      hour.close = @norm hour.close
      hour.last_order = @norm hour.last_order
      hour.id = id
      hours.push hour
      id += 1
    @set 'hours', hours

  norm: (raw) ->
    while raw.length < 4
      raw = '0' + raw
    raw

  addHour: ->
    console.log 'store: addHour'
    hour = { id: (new Date).getTime(), open: "0000", close: "0000", last_order: "0000", \
             wd0: 0, wd1: 0, wd2: 0, wd3: 0, wd4: 0, wd5: 0, wd6: 0 }
    @set 'hours', @state.hours.concat([hour])

  removeHour: (payload) ->
    console.log "store: removeHour: #{JSON.stringify payload}"
    hours = (h for h in @state.hours when payload.id != h.id)
    @set 'hours', hours

  updateHour: (payload) ->
    console.log "store: updateHour: #{JSON.stringify payload}"
    hour = (h for h in @state.hours when payload.id == h.id)[0]
    $.extend hour, payload.updates
    @emit 'change'

Dispatcher = Flux.createDispatcher

  loadHours: ->
    @dispatch 'hour:load'

  addHour: ->
    @dispatch 'hour:add'

  removeHour: (id) ->
    @dispatch 'hour:remove', { id: id }

  updateHour: (id, updates) ->
    @dispatch 'hour:update', { id: id, updates: updates }

  getStores: ->
    {
      admin: Store
    }

ActionCreator =

  loadHours: ->
    console.log 'action: loadHours'
    do Dispatcher.loadHours

  addHour: ->
    console.log 'action: addHour'
    do Dispatcher.addHour

  removeHour: (id) ->
    console.log "action: removeHour: #{id}"
    Dispatcher.removeHour id

  updateHour: (id, updates) ->
    console.log "action: updateHour: #{id}, #{updates}"
    Dispatcher.updateHour id, updates

Header = React.createClass

  handleClick: (e) ->
    do e.preventDefault
    do ActionCreator.addHour

  render: ->
    <h2>
      Edit Shop Hours
      <a href="#" className="add-new-h2" onClick={@handleClick}>Add New</a>
    </h2>

ShopHour = React.createClass

  weekdays: ->
    { wd0: 'Sun', wd1: 'Mon', wd2: 'Tue', wd3: 'Wed', wd4: 'Thu', wd5: 'Fri', wd6: 'Sat' }

  handleClick: (e) ->
    do e.preventDefault
    ActionCreator.removeHour @props.hour.id

  handleChange: (e) ->
    name = e.target.getAttribute('data-tag')
    updates = {}
    updates[name] = if e.target.checked then 1 else 0
    ActionCreator.updateHour @props.hour.id, updates

  render: ->
    <li>
      <input type="text" name="hour_open_h[]" defaultValue={@props.hour.open.substr(0, 2)} size="2" maxLength="2" />
      <span className="sep">:</span>
      <input type="text" name="hour_open_m[]" defaultValue={@props.hour.open.substr(2, 2)} size="2" maxLength="2" />
      <span className="sep">-</span>
      <input type="text" name="hour_close_h[]" defaultValue={@props.hour.close.substr(0, 2)} size="2" maxLength="2" />
      <span className="sep">:</span>
      <input type="text" name="hour_close_m[]" defaultValue={@props.hour.close.substr(2, 2)} size="2" maxLength="2" />

      <span>(L.O. </span>
      <input type="text" name="hour_last_h[]" defaultValue={@props.hour.last_order.substr(0, 2)} size="2" maxLength="2" />
      <span className="sep">:</span>
      <input type="text" name="hour_last_m[]" defaultValue={@props.hour.last_order.substr(2, 2)} size="2" maxLength="2" />
      <span>)</span>

      <span className="wd-group">
        {Object.keys(@weekdays()).map (wd) =>
          <label key={wd} className="syrup-toggle">
            <input type="hidden" name={"hour_#{wd}[]"} value={@props.hour[wd]} />
            <input type="checkbox" data-tag={wd} defaultChecked={if @props.hour[wd].toString() == '1' then 'checked' else ''} onChange={@handleChange} />
            <span>{@weekdays()[wd]}</span>
          </label>
        }
      </span>

      <a href="#" className="delete" onClick={@handleClick}>Delete</a>
    </li>

ShopHourList = React.createClass

  render: ->
    <ul>
      {@props.hours.map (hour) ->
        <ShopHour key={hour.id} hour={hour} />
      }
    </ul>

ShopHoursEditor = React.createClass

  mixins: [Flux.mixins.storeListener]

  componentDidMount: ->
    do ActionCreator.loadHours

  render: ->
    store = @getStore 'admin'

    <div>
      <Header />
      <ShopHourList hours={store.hours} />
    </div>

setTimeout ->

  if node = document.getElementById('syrup-shop-hours-editor')
    window.mainView = React.render <ShopHoursEditor dispatcher={Dispatcher} />, node

  # Location Mini Map
  $('.syrup-location-preview').each ->
    root = $(@)
    getPos = ->
      lat = root.find('input.lat').val()
      lng = root.find('input.lng').val()
      if lat == '0' && lng == '0'
        lat = '35.680907802'
        lng = '139.767122085'
      new google.maps.LatLng parseFloat(lat), parseFloat(lng)

    setPos = (pos) ->
      root.find('input.lat').val(pos.lat())
      root.find('input.lng').val(pos.lng())

    update = ->
      id = root.data('map')
      pos = getPos()
      map = root.data('instance')
      if !map
        map = new google.maps.Map(document.getElementById(id), { zoom: 13 })
        root.data('instance', map)
      map.setCenter(pos)
      marker = root.data('marker')
      if !marker
        marker = new google.maps.Marker({
          position: pos,
          map: map,
          draggable: true
        })
        google.maps.event.addListener marker, 'dragend', ->
          setPos(marker.getPosition())
        root.data('marker', marker)
      marker.setPosition(pos)

    root.find('input.lat').keyup(-> do update)
    root.find('input.lng').keyup(-> do update)
    update()

, 200
