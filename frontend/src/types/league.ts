export type Team = {
  id: number
  name: string
  power: number
}

export type TableRow = {
  teamId: number
  name: string
  power: number
  played: number
  won: number
  drawn: number
  lost: number
  goalsFor: number
  goalsAgainst: number
  goalDifference: number
  points: number
}

export type Fixture = {
  id: number
  week: number
  home_goals: number | null
  away_goals: number | null
  home_team_id: number
  away_team_id: number
  home_team: Team
  away_team: Team
}

export type Prediction = {
  teamId: number
  name: string
  percentage: number
}

export type LeagueState = {
  meta: {
    totalWeeks: number
    currentWeek: number
    remainingWeeks: number
    isFinished: boolean
  }
  teams: Team[]
  table: TableRow[]
  fixturesByWeek: Fixture[][]
  predictions: Prediction[]
}

export type TeamSeed = {
  name: string
  power: number
}

export type PredictionDisplayRow = {
  name: string
  percentage: number
}
