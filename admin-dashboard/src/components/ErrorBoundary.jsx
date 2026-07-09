import { Component } from 'react'

/**
 * Catches render-time crashes so one broken page doesn't blank the whole
 * dashboard. Offers reload; logs to console for diagnosis.
 */
export default class ErrorBoundary extends Component {
  constructor(props) {
    super(props)
    this.state = { error: null }
  }

  static getDerivedStateFromError(error) {
    return { error }
  }

  componentDidCatch(error, info) {
    console.error('Dashboard error boundary caught:', error, info)
  }

  render() {
    if (this.state.error) {
      return (
        <div className="min-h-[50vh] flex flex-col items-center justify-center text-center p-8">
          <h2 className="text-lg font-semibold text-gray-800 mb-2">
            Samahani, kuna hitilafu / Something went wrong
          </h2>
          <p className="text-sm text-gray-500 mb-4 max-w-md">
            This page failed to render. The rest of the dashboard is unaffected.
          </p>
          <button
            onClick={() => { this.setState({ error: null }); window.location.reload() }}
            className="px-4 py-2 bg-green-700 text-white rounded-lg text-sm hover:bg-green-800"
          >
            Reload page
          </button>
        </div>
      )
    }
    return this.props.children
  }
}
