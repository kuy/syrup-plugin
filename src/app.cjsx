window.React = React = require 'react'
Flux = require('delorean').Flux
Router = require('director').Router

TagSelector = React.createClass

  mixins: [Flux.mixins.storeListener]

  render: ->
    <ul>
      <li>Nyaa</li>
    </ul>

SyrupTagDispatcher = Flux.createDispatcher

  selectTag: (tag) ->
    @dispatch 'tag:select', tag

SyrupActionCreator =

  selectTag: (tag) ->
    SyrupTagDispatcher.selectTag tag

setTimeout ->
  window.mainView = React.render <TagSelector dispatcher={SyrupTagDispatcher} />,
    document.getElementById('syrup-container')

  appRouter = new Router

    '/': ->
      SyrupActionCreator.selectTag({tag: 'Tokyo'})

  appRouter.init '/'
, 500
