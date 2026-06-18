package com.example.project2.ui.components

import androidx.compose.material.icons.Icons
import androidx.compose.material.icons.outlined.Search
import androidx.compose.runtime.Composable
import androidx.compose.ui.Modifier

@Composable
fun BankSearchField(
    value: String,
    onValueChange: (String) -> Unit,
    modifier: Modifier = Modifier
) {
    BankTextField(
        value = value,
        onValueChange = onValueChange,
        label = "Buscar por titulo",
        modifier = modifier,
        leadingIcon = Icons.Outlined.Search
    )
}
