package com.example.project2.ui.components

import androidx.compose.foundation.layout.Arrangement
import androidx.compose.foundation.layout.Column
import androidx.compose.foundation.layout.ColumnScope
import androidx.compose.foundation.layout.Row
import androidx.compose.foundation.layout.fillMaxWidth
import androidx.compose.foundation.layout.padding
import androidx.compose.material3.MaterialTheme
import androidx.compose.material3.Text
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier
import androidx.compose.ui.unit.dp
import com.example.project2.ui.theme.TextPrimary
import com.example.project2.ui.theme.TextSecondary

@Composable
fun InfoRow(label: String, value: String?, modifier: Modifier = Modifier) {
    Column(modifier = modifier.fillMaxWidth()) {
        Text(text = label, color = TextSecondary, style = MaterialTheme.typography.labelMedium)
        Text(
            text = value?.takeIf { it.isNotBlank() } ?: "No especificado",
            color = TextPrimary,
            style = MaterialTheme.typography.bodyLarge
        )
    }
}

@Composable
fun InfoSectionCard(
    title: String,
    modifier: Modifier = Modifier,
    content: @Composable ColumnScope.() -> Unit
) {
    BankGlassCard(modifier = modifier.fillMaxWidth()) {
        Text(text = title, color = TextPrimary, style = MaterialTheme.typography.titleMedium)
        Column(
            modifier = Modifier.padding(top = 14.dp),
            verticalArrangement = Arrangement.spacedBy(12.dp),
            content = content
        )
    }
}

@Composable
fun TwoColumnInfoRow(firstLabel: String, firstValue: String?, secondLabel: String, secondValue: String?) {
    Row(horizontalArrangement = Arrangement.spacedBy(14.dp)) {
        InfoRow(firstLabel, firstValue, Modifier.weight(1f))
        InfoRow(secondLabel, secondValue, Modifier.weight(1f))
    }
}
